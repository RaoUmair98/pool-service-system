<x-mail::message>
# Invoice {{ $invoice->invoice_number }}

Hi {{ $invoice->customer->name }},

Please find your invoice for **{{ $invoice->period_label }}** attached as a PDF.

**Amount due:** {{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}  
@if ($invoice->due_at)
**Due by:** {{ $invoice->due_at->format('d M Y') }}
@endif

Pay by cash, bank transfer or cheque, then we’ll mark it paid on our side.

Thanks,<br>
{{ $invoice->company->name }}
</x-mail::message>
