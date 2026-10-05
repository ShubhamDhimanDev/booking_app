{{-- Fires a Meta Pixel and a GA4 event using the site-wide IDs (Admin > Tracking Settings).
     Vars: $meta (Pixel event name), $ga (GA4 event name), $currency, $value, $items = [['id','name','price','qty'], ...] --}}
@php
    $contentIds = array_map(fn ($i) => (string) $i['id'], $items);
    $metaParams = [
        'content_type' => 'product',
        'content_ids' => $contentIds,
        'contents' => array_map(fn ($i) => ['id' => (string) $i['id'], 'quantity' => $i['qty']], $items),
        'num_items' => array_sum(array_column($items, 'qty')),
        'value' => round($value, 2),
        'currency' => $currency,
    ];
    if (count($items) === 1) {
        $metaParams['content_name'] = $items[0]['name'];
    }
    $gaParams = [
        'currency' => $currency,
        'value' => round($value, 2),
        'items' => array_map(fn ($i) => ['item_id' => (string) $i['id'], 'item_name' => $i['name'], 'price' => round($i['price'], 2), 'quantity' => $i['qty']], $items),
    ];
    if (! empty($transactionId)) {
        $gaParams['transaction_id'] = $transactionId;
        $metaParams['order_id'] = $transactionId;
    }
@endphp
{!! \App\Services\TrackingService::getEventScript($meta, $metaParams) !!}
{!! \App\Services\TrackingService::getGoogleEventScript($ga, $gaParams) !!}
