<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceGenerator;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $invoices = Invoice::query()
            ->forCompany($companyId)
            ->with('customer')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->period_year, fn ($q, $year) => $q->where('period_year', $year))
            ->when($request->period_month, fn ($q, $month) => $q->where('period_month', $month))
            ->when($request->search, function ($query, $search) {
                $query->where(fn ($q) => $q->where('invoice_number', 'like', "%$search%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$search%")));
            })
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('id')
            ->paginate($this->perPage($request, 25))
            ->withQueryString();

        $customers = Customer::query()
            ->forCompany($companyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $this->infiniteOrView(
            $request,
            'invoices.index',
            'invoices.rows',
            compact('invoices', 'customers'),
            'invoices',
        );
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->ensureSameCompany($request, $invoice);
        $invoice->load(['customer', 'items.pool', 'company']);

        return view('invoices.show', compact('invoice'));
    }

    public function generate(Request $request, InvoiceGenerator $generator)
    {
        $data = $request->validate([
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $result = $generator->generateForCompany(
            $request->user()->company,
            (int) ($data['year'] ?? now()->format('Y')),
            (int) ($data['month'] ?? now()->format('n')),
        );

        return back()->with('success', "Created {$result['created']} invoice(s); {$result['skipped']} already existed.");
    }

    public function pdf(Request $request, Invoice $invoice, InvoiceService $invoices)
    {
        $this->ensureSameCompany($request, $invoice);

        return $invoices->pdf($invoice)->download($invoice->invoice_number.'.pdf');
    }

    public function send(Request $request, Invoice $invoice, InvoiceService $invoices)
    {
        $this->ensureSameCompany($request, $invoice);

        // Send immediately so admin sees the result; still logs via EmailLog.
        $log = $invoices->sendNow($invoice);

        $message = match ($log->status) {
            'sent' => 'Invoice emailed to '.$log->to_email.'.',
            'skipped' => 'Invoice not sent: '.$log->error,
            'failed' => 'Failed to send: '.$log->error,
            default => 'Invoice queued.',
        };

        return back()->with($log->status === 'sent' ? 'success' : 'error', $message);
    }

    public function markPaid(Request $request, Invoice $invoice, InvoiceService $invoices)
    {
        $this->ensureSameCompany($request, $invoice);

        $data = $request->validate([
            'payment_method' => ['nullable', 'string', Rule::in(['cash', 'bank_transfer', 'cheque', 'other'])],
            'payment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $invoices->markPaidAllowingDraft(
            $invoice,
            $data['payment_method'] ?? null,
            $data['payment_note'] ?? null,
        );

        return back()->with('success', 'Invoice marked as paid.');
    }
}
