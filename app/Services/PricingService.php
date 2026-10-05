<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\PromoCode;

/**
 * Single source of truth for cart totals. The browser never sends amounts.
 *
 * There is no shipping and no tax: total = sum(sale price x qty) - promo discount.
 */
class PricingService
{
    /**
     * @return array{
     *   lines: array<int, array>, currency: string, count: int,
     *   subtotal: float, mrp_total: float, savings: float,
     *   promo: ?PromoCode, promo_error: ?string, discount: float, total: float, has_issues: bool
     * }
     */
    public function summarize(Cart $cart): array
    {
        $cart->loadMissing('country');
        $country = $cart->country;

        $items = $cart->items()->with(['product.prices' => fn ($q) => $q->where('country_id', $country->id)])->get();

        $lines = [];
        $subtotal = $mrpTotal = 0.0;
        $count = 0;
        $hasIssues = false;

        foreach ($items as $item) {
            $product = $item->product;
            $price = $product && $product->is_active ? $product->priceFor($country) : null;

            $line = [
                'item' => $item,
                'product' => $product,
                'quantity' => $item->quantity,
                'price' => $price ? (float) $price->sale_price : 0.0,
                'mrp' => $price ? (float) $price->mrp : 0.0,
                'line_total' => 0.0,
                'line_mrp' => 0.0,
                'error' => null,
            ];

            if (! $price) {
                $line['error'] = 'This product is no longer available.';
                $hasIssues = true;
            } else {
                if (! $product->inStock($item->quantity)) {
                    $line['error'] = $product->stock_qty > 0
                        ? 'Only ' . $product->stock_qty . ' left in stock.'
                        : 'Out of stock.';
                    $hasIssues = true;
                }
                $line['line_total'] = round($line['price'] * $item->quantity, 2);
                $line['line_mrp'] = round($line['mrp'] * $item->quantity, 2);
                $subtotal += $line['line_total'];
                $mrpTotal += $line['line_mrp'];
                $count += $item->quantity;
            }

            $lines[] = $line;
        }

        $subtotal = round($subtotal, 2);
        $mrpTotal = round($mrpTotal, 2);

        [$promo, $discount, $promoError] = $this->promo($cart->promo_code, $subtotal);

        return [
            'lines' => $lines,
            'currency' => $country->currency,
            'count' => $count,
            'subtotal' => $subtotal,
            'mrp_total' => $mrpTotal,
            'savings' => round($mrpTotal - $subtotal, 2),
            'promo' => $promo,
            'promo_error' => $promoError,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
            'has_issues' => $hasIssues,
        ];
    }

    /** @return array{0: ?PromoCode, 1: float, 2: ?string} */
    public function promo(?string $code, float $subtotal): array
    {
        if (! $code) {
            return [null, 0.0, null];
        }

        $promo = PromoCode::where('code', strtoupper($code))->first();
        if (! $promo || ! $promo->isValid()) {
            return [null, 0.0, 'This promo code is no longer valid.'];
        }
        if ($promo->min_booking_amount && $subtotal < (float) $promo->min_booking_amount) {
            return [null, 0.0, 'Add items worth at least ' . number_format((float) $promo->min_booking_amount, 2) . ' to use ' . $promo->code . '.'];
        }

        $discount = min(round($promo->calculateDiscount($subtotal), 2), $subtotal);

        return [$promo, $discount, null];
    }
}
