<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        .muted { color: #64748b; }
        .header { margin-bottom: 24px; }
        .meta td { padding: 2px 8px 2px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.items th, table.items td { border-bottom: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        table.items th { background: #f1f5f9; }
        .right { text-align: right; }
        .total { font-size: 16px; font-weight: bold; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $invoice->company->name }}</h1>
        <div class="muted">Invoice {{ $invoice->invoice_number }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Bill to</strong></td>
            <td>
                {{ $invoice->customer->name }}<br>
                {{ $invoice->customer->service_address }}
                @if ($invoice->customer->email)<br>{{ $invoice->customer->email }}@endif
            </td>
        </tr>
        <tr>
            <td><strong>Period</strong></td>
            <td>{{ $invoice->period_label }}</td>
        </tr>
        <tr>
            <td><strong>Issued</strong></td>
            <td>{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Due</strong></td>
            <td>{{ $invoice->due_at?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td>{{ $invoice->status_label }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $invoice->currency }} {{ number_format((float) $item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total right">Total: {{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</p>

    @if ($invoice->notes)
        <p><strong>Notes:</strong> {{ $invoice->notes }}</p>
    @endif
</body>
</html>
