<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * InvoiceService
 *
 * Handles invoice generation, PDF creation, and management
 */
class InvoiceService
{
    /**
     * Create invoice for subscription
     */
    public function createInvoiceForSubscription(Subscription $subscription): Invoice
    {
        $plan = $subscription->plan;
        $org = $subscription->organization;

        $invoice = Invoice::create([
            'organization_id' => $org->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'line_items' => [
                [
                    'description' => $plan->name . ' Plan - ' . ucfirst($subscription->billing_cycle),
                    'quantity' => 1,
                    'unit_price' => $subscription->amount,
                    'total' => $subscription->amount,
                ]
            ],
            'subtotal' => $subscription->amount,
            'tax_amount' => $subscription->amount * 0.18, // 18% GST
            'discount_amount' => 0,
            'total_amount' => $subscription->amount * 1.18,
            'currency' => $subscription->currency,
            'status' => Invoice::STATUS_PENDING,
            'billing_address' => $org->billing_address,
            'billing_email' => $org->billing_email ?: $org->contact_email,
            'gstin' => $org->gstin,
        ]);

        Log::info('Invoice created', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'organization_id' => $org->id,
        ]);

        return $invoice;
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePDF(Invoice $invoice): string
    {
        try {
            $org = $invoice->organization;
            $subscription = $invoice->subscription;

            $data = [
                'invoice' => $invoice,
                'organization' => $org,
                'subscription' => $subscription,
                'platform_name' => config('app.name', 'MeetFlow'),
                'platform_address' => config('app.invoice_address', ''),
                'platform_email' => config('app.invoice_email', 'billing@meetflow.com'),
                'platform_phone' => config('app.invoice_phone', ''),
                'platform_gstin' => config('app.gstin', ''),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('invoices.pdf', $data);

            // Save PDF to storage
            $filename = "invoices/{$org->id}/invoice-{$invoice->invoice_number}.pdf";
            Storage::put($filename, $pdf->output());

            // Update invoice with PDF path
            $invoice->update(['pdf_path' => $filename]);

            Log::info('Invoice PDF generated', [
                'invoice_id' => $invoice->id,
                'pdf_path' => $filename,
            ]);

            return $filename;

        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send invoice via email
     */
    public function sendInvoice(Invoice $invoice): void
    {
        try {
            // Ensure PDF is generated
            if (!$invoice->pdf_path) {
                $this->generatePDF($invoice);
            }

            // Send email (implement your mail class)
            // Mail::to($invoice->billing_email)->send(new InvoiceMail($invoice));

            Log::info('Invoice sent', [
                'invoice_id' => $invoice->id,
                'email' => $invoice->billing_email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice, string $razorpayPaymentId): void
    {
        $invoice->markAsPaid($razorpayPaymentId);

        Log::info('Invoice marked as paid', [
            'invoice_id' => $invoice->id,
            'razorpay_payment_id' => $razorpayPaymentId,
        ]);
    }

    /**
     * Create credit note (refund invoice)
     */
    public function createCreditNote(Invoice $originalInvoice, float $amount, string $reason): Invoice
    {
        $creditNote = Invoice::create([
            'organization_id' => $originalInvoice->organization_id,
            'subscription_id' => $originalInvoice->subscription_id,
            'invoice_number' => 'CN-' . Invoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now(),
            'line_items' => [
                [
                    'description' => 'Credit Note - ' . $reason,
                    'quantity' => 1,
                    'unit_price' => -$amount,
                    'total' => -$amount,
                ]
            ],
            'subtotal' => -$amount,
            'tax_amount' => -($amount * 0.18),
            'discount_amount' => 0,
            'total_amount' => -($amount * 1.18),
            'currency' => $originalInvoice->currency,
            'status' => Invoice::STATUS_PAID,
            'paid_at' => now(),
            'billing_address' => $originalInvoice->billing_address,
            'billing_email' => $originalInvoice->billing_email,
            'gstin' => $originalInvoice->gstin,
        ]);

        Log::info('Credit note created', [
            'credit_note_id' => $creditNote->id,
            'original_invoice_id' => $originalInvoice->id,
            'amount' => $amount,
        ]);

        return $creditNote;
    }

    /**
     * Get organization invoices
     */
    public function getOrganizationInvoices(Organization $org, int $perPage = 15)
    {
        return Invoice::where('organization_id', $org->id)
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get total revenue for period
     */
    public function getTotalRevenue(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        return Invoice::where('status', Invoice::STATUS_PAID)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('total_amount');
    }

    /**
     * Get outstanding invoices
     */
    public function getOutstandingInvoices()
    {
        return Invoice::where('status', Invoice::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->with('organization')
            ->get();
    }

    /**
     * Send payment reminder for overdue invoices
     */
    public function sendPaymentReminders(): int
    {
        $overdueInvoices = $this->getOutstandingInvoices();
        $count = 0;

        foreach ($overdueInvoices as $invoice) {
            try {
                $this->sendInvoice($invoice);
                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to send payment reminder', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}
