<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Invoice Model
 *
 * Manages subscription invoices and payments
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $subscription_id
 * @property string $invoice_number
 * @property Carbon $invoice_date
 * @property Carbon|null $due_date
 * @property array $line_items
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $discount_amount
 * @property float $total_amount
 * @property string $currency
 * @property string $status
 * @property Carbon|null $paid_at
 * @property string $gateway
 * @property string|null $gateway_payment_id
 * @property string|null $gateway_order_id
 * @property string|null $gateway_invoice_id
 * @property array|null $payment_metadata
 * @property string|null $pdf_path
 * @property string|null $billing_address
 * @property string|null $billing_email
 * @property string|null $gstin
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'subscription_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'line_items',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'paid_at',
        'gateway',
        'gateway_payment_id',
        'gateway_order_id',
        'gateway_invoice_id',
        'payment_metadata',
        'pdf_path',
        'billing_address',
        'billing_email',
        'gstin',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'line_items' => 'array',
        'payment_metadata' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    // ==================== RELATIONSHIPS ====================

    /**
     * The organization this invoice belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The subscription this invoice is for
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope: Pending invoices
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('due_date', '<', now());
    }

    /**
     * Scope: Recent invoices
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->isPending()
            && $this->due_date
            && $this->due_date->isPast();
    }

    /**
     * Check if invoice is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(string $razorpayPaymentId = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'razorpay_payment_id' => $razorpayPaymentId,
        ]);
    }

    /**
     * Mark invoice as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Generate next invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');

        // Get last invoice number for this month
        $lastInvoice = self::whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract sequence number from last invoice
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '₹' . number_format($this->subtotal, 2);
    }

    /**
     * Get formatted tax amount
     */
    public function getFormattedTaxAttribute(): string
    {
        return '₹' . number_format($this->tax_amount, 2);
    }

    /**
     * Get download URL for PDF
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return asset('storage/' . $this->pdf_path);
    }

    /**
     * Add line item to invoice
     */
    public function addLineItem(string $description, int $quantity, float $unitPrice): void
    {
        $lineItems = $this->line_items ?? [];

        $lineItems[] = [
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
        ];

        $this->line_items = $lineItems;
        $this->recalculateTotals();
    }

    /**
     * Recalculate invoice totals
     */
    protected function recalculateTotals(): void
    {
        $subtotal = 0;

        foreach ($this->line_items ?? [] as $item) {
            $subtotal += $item['total'] ?? 0;
        }

        $this->subtotal = $subtotal;

        // Calculate tax (18% GST for India)
        $this->tax_amount = $subtotal * 0.18;

        // Total = Subtotal + Tax - Discount
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
    }
}
