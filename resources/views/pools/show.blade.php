@extends('layouts.admin')

@section('title', $pool->displayName())

@section('actions')
    <a href="{{ route('pools.edit', $pool) }}" class="btn btn-primary"><i class="mdi mdi-pencil"></i> Edit</a>
    @can('manage schedules')
        <a href="{{ route('pools.schedule.edit', $pool) }}" class="btn btn-success"><i class="mdi mdi-calendar-clock"></i> Schedule</a>
    @endcan
    <a href="{{ route('customers.show', $pool->customer) }}" class="btn btn-light">Back to customer</a>
@endsection

@section('content')
    @php
        [$typeLabel, $typeBadge, $typeIcon] = \App\Models\Pool::TYPES[$pool->type] ?? [$pool->type, 'secondary', 'mdi-pool'];
        $currency = $pool->company->currency ?? 'USD';
        $schedule = $pool->schedule;
    @endphp

    <div class="detail-layout">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <h4 class="mt-0 mb-1">{{ $pool->displayName() }}</h4>
                            <span class="badge badge-{{ $typeBadge }}"><i class="mdi {{ $typeIcon }}"></i> {{ $typeLabel }}</span>
                            <span class="badge badge-{{ $pool->is_active ? 'success' : 'secondary' }}">{{ $pool->is_active ? 'Active' : 'Turned off' }}</span>
                        </div>
                        @if ($pool->photoUrl())
                            <img src="{{ $pool->photoUrl() }}" alt="Pool photo" class="border rounded" style="max-height: 72px; max-width: 120px;">
                        @endif
                    </div>

                    <div class="form-grid">
                        <div class="span-4">
                            <dl class="detail-dl">
                                <dt><i class="mdi mdi-account-outline"></i> Customer</dt>
                                <dd><a href="{{ route('customers.show', $pool->customer) }}">{{ $pool->customer->name }}</a></dd>
                            </dl>
                        </div>
                        <div class="span-4">
                            <dl class="detail-dl">
                                <dt><i class="mdi mdi-cup-water"></i> Size</dt>
                                <dd>{{ $pool->gallons ? number_format($pool->gallons).' gallons' : '—' }}</dd>
                            </dl>
                        </div>
                        <div class="span-4">
                            <dl class="detail-dl">
                                <dt><i class="mdi mdi-currency-usd"></i> Monthly rate</dt>
                                <dd>{{ $currency }} {{ number_format((float) $pool->monthly_rate, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="header-title"><i class="mdi mdi-wrench-outline"></i> Equipment</h4>
                    @if ($pool->equipment->isEmpty())
                        <p class="text-muted mb-0">No equipment listed yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pool->equipment as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->notes ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0"><i class="mdi mdi-calendar-clock"></i> Weekly schedule</h4>
                    @can('manage schedules')
                        <a href="{{ route('pools.schedule.edit', $pool) }}" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-pencil"></i> {{ $schedule ? 'Edit' : 'Set' }}
                        </a>
                    @endcan
                </div>

                @if ($schedule)
                    <dl class="detail-dl">
                        <dt>Day</dt>
                        <dd>{{ $schedule->weekday_label }}</dd>
                        <dt>Technician</dt>
                        <dd>{{ $schedule->technician?->name ?? '—' }}</dd>
                        <dt>Status</dt>
                        <dd>
                            <span class="badge badge-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                {{ $schedule->is_active ? 'Active' : 'Off' }}
                            </span>
                        </dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">No weekly schedule yet.
                        @can('manage schedules')
                            <a href="{{ route('pools.schedule.edit', $pool) }}">Set service day and technician</a>.
                        @endcan
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
