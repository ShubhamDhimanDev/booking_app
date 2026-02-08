<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of all invoices
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with(['organization', 'subscription'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('organization', function ($org) use ($search) {
                          $org->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->latest('invoice_date')
            ->paginate(20);

        return view('super-admin.invoices.index', compact('invoices'));
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['organization', 'subscription']);

        return view('super-admin.invoices.show', compact('invoice'));
    }

    /**
     * Download invoice as PDF
     */
    public function download(Invoice $invoice)
    {
        $invoice->load(['organization', 'subscription']);

        $pdf = Pdf::loadView('super-admin.invoices.pdf', compact('invoice'));

        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
