@foreach ($visits as $visit)
    <tr>
        <td>{{ $visit->scheduled_date->format('D, d M Y') }}</td>
        <td>
            <a href="{{ route('customers.show', $visit->customer) }}">{{ $visit->customer->name }}</a>
            <div class="small text-muted text-wrap" style="max-width: 220px;">{{ $visit->customer->service_address }}</div>
        </td>
        <td><a href="{{ route('pools.show', $visit->pool) }}">{{ $visit->pool->displayName() }}</a></td>
        <td>{{ $visit->technician->name }}</td>
        <td>
            <span class="badge badge-{{ $visit->status_badge }}">
                <i class="mdi {{ $visit->status_icon }}"></i> {{ $visit->status_label }}
            </span>
        </td>
        <td>
            <a href="{{ route('visits.show', $visit) }}" class="btn btn-xs btn-light"><i class="mdi mdi-eye-outline"></i> Open</a>
        </td>
    </tr>
@endforeach
