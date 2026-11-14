@foreach ($logs as $log)
    <tr>
        <td class="text-nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td>
        <td>{{ $log->type_label }}</td>
        <td>
            {{ $log->to_email }}
            @if ($log->customer)
                <div class="small text-muted">{{ $log->customer->name }}</div>
            @endif
        </td>
        <td class="text-wrap" style="max-width: 260px;">{{ $log->subject }}</td>
        <td><span class="badge badge-{{ $log->status_badge }}">{{ $log->status_label }}</span></td>
        <td class="text-wrap" style="max-width: 220px;">
            @if ($log->error)
                <span class="text-danger small">{{ $log->error }}</span>
            @elseif ($log->sent_at)
                <span class="text-muted small">Sent {{ $log->sent_at->format('d M Y, h:i A') }}</span>
            @else
                —
            @endif
            @if ($log->related_id && $log->type === \App\Models\EmailLog::TYPE_SERVICE_REPORT)
                <div><a href="{{ route('visits.show', $log->related_id) }}" class="small">Open visit</a></div>
            @elseif ($log->related_id && $log->type === \App\Models\EmailLog::TYPE_INVOICE)
                <div><a href="{{ route('invoices.show', $log->related_id) }}" class="small">Open invoice</a></div>
            @endif
        </td>
    </tr>
@endforeach
