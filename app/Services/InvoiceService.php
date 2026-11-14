<?php

namespace App\Services;

use App\Jobs\SendInvoiceMailJob;
use App\Mail\InvoiceMail;
use App\Models\EmailLog;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class InvoiceService
{
    public function pdf(Invoice $invoice)
    {
        $invoice->loadMissing(['customer', 'company', 'items.pool']);

        return Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
    }

    public function queueSend(Invoice $invoice): void
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            throw ValidationException::withMessages([
                'status' => 'Paid invoices cannot be sent again.',
            ]);
        }

        SendInvoiceMailJob::dispatch($invoice->id);
    }

    public function sendNow(Invoice $invoice): EmailLog
    {
        $invoice->loadMissing(['customer', 'company', 'items']);

        $to = trim((string) $invoice->customer?->email);
        $subject = 'Invoice '.$invoice->invoice_number.' — '.$invoice->company->name;

        $log = EmailLog::create([
            'company_id' => $invoice->company_id,
            'customer_id' => $invoice->customer_id,
            'related_type' => $invoice->getMorphClass(),
            'related_id' => $invoice->id,
            'to_email' => $to !== '' ? $to : '(none)',
            'subject' => $subject,
            'type' => EmailLog::TYPE_INVOICE,
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        if ($to === '') {
            $log->update([
                'status' => EmailLog::STATUS_SKIPPED,
                'error' => 'Customer has no email address.',
            ]);

            return $log->fresh();
        }

        try {
            $pdf = $this->pdf($invoice)->output();

            Mail::to($to)->send(new InvoiceMail($invoice, $pdf));

            $invoice->update([
                'status' => $invoice->status === Invoice::STATUS_PAID
                    ? Invoice::STATUS_PAID
                    : Invoice::STATUS_SENT,
                'sent_at' => $invoice->sent_at ?? now(),
            ]);

            $log->update([
                'status' => EmailLog::STATUS_SENT,
                'sent_at' => now(),
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => EmailLog::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }

        return $log->fresh();
    }

    public function markPaid(Invoice $invoice, ?string $method = null, ?string $note = null): Invoice
    {
        if ($invoice->status === Invoice::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'status' => 'Send the invoice before marking it paid, or send it first.',
            ]);
        }

        if ($invoice->isPaid()) {
            return $invoice;
        }

        $invoice->update([
            'status' => Invoice::STATUS_PAID,
            'paid_at' => now(),
            'payment_method' => $method,
            'payment_note' => $note,
        ]);

        return $invoice->fresh();
    }

    /**
     * Allow marking draft as paid after offline payment without email (optional flow).
     * Spec: draft → send → paid. For cash at office, admin may send then mark paid.
     * Also allow draft → paid for simplicity when payment collected without email.
     */
    public function markPaidAllowingDraft(Invoice $invoice, ?string $method = null, ?string $note = null): Invoice
    {
        $invoice->update([
            'status' => Invoice::STATUS_PAID,
            'paid_at' => now(),
            'payment_method' => $method,
            'payment_note' => $note,
            'issued_at' => $invoice->issued_at ?? now()->toDateString(),
        ]);

        return $invoice->fresh();
    }
}
