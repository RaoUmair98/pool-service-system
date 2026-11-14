@foreach ($invoices as $invoice)
    <tr>
        <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
        <td>{{ $invoice->customer->name }}</td>
        <td>{{ $invoice->period_label }}</td>
        <td>{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</td>
        <td>
            <span class="badge badge-{{ $invoice->status_badge }}">
                <i class="mdi {{ $invoice->status_icon }}"></i> {{ $invoice->status_label }}
            </span>
        </td>
        <td>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-xs btn-light"><i class="mdi mdi-eye-outline"></i> Open</a>
            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-xs btn-light"><i class="mdi mdi-download"></i> PDF</a>
        </td>
    </tr>
@endforeach
