<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $organization = auth()->user()->organization;

        $invoices = $organization->invoices()
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('invoice_date', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('invoice_date', '<=', $date);
            })
            ->latest('invoice_date')
            ->paginate(20);

        return view('organization.billing.invoices', compact('invoices'));
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['organization', 'subscription']);

        return view('organization.billing.invoice-detail', compact('invoice'));
    }

    /**
     * Download invoice as PDF
     */
    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        if ($invoice->status !== 'paid') {
            return back()->with('error', 'Only paid invoices can be downloaded!');
        }

        $invoice->load(['organization', 'subscription']);

        $pdf = Pdf::loadView('organization.billing.invoice-pdf', compact('invoice'));

        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
