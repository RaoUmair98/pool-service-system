<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendInvoiceMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $invoiceId) {}

    public function handle(InvoiceService $invoices): void
    {
        $invoice = Invoice::query()->find($this->invoiceId);

        if (! $invoice || $invoice->status === Invoice::STATUS_PAID) {
            return;
        }

        $invoices->sendNow($invoice);
    }
}
