@foreach ($logs as $log)
    <tr>
        <td class="text-nowrap">
            <strong>{{ $log->created_at->format('d M Y') }}</strong><br>
            <span class="text-muted">{{ $log->created_at->format('h:i:s A') }}</span>
        </td>
        <td class="text-nowrap">
            {{ $log->user?->name ?? $log->user_name ?? 'Not logged in' }}
            @if ($log->ip_address)
                <br><small class="text-muted">{{ $log->ip_address }}</small>
            @endif
        </td>
        <td class="text-nowrap">
            <span class="badge badge-{{ $log->action_badge }}"><i class="mdi {{ $log->action_icon }}"></i> {{ $log->action_label }}</span>
        </td>
        <td class="name-cell">{{ $log->subject_label ?? '-' }}</td>
        <td>
            @forelse ($log->changes ?? [] as $field => $change)
                <div class="log-change">
                    <span class="log-field">{{ $field }}:</span>
                    @if (($change['old'] ?? null) !== null)
                        <span class="log-old">{{ $change['old'] }}</span>
                        <i class="mdi mdi-arrow-right"></i>
                    @endif
                    <span class="log-new">{{ $change['new'] ?? 'empty' }}</span>
                </div>
            @empty
                <span class="text-muted">-</span>
            @endforelse
        </td>
        <td class="text-wrap">{{ $log->reason ?: '-' }}</td>
    </tr>
@endforeach
