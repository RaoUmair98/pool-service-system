@extends('layouts.admin')

@section('title', $customer->name)

@section('actions')
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary"><i class="mdi mdi-pencil"></i> Edit</a>
    @can('manage pools')
        <a href="{{ route('customers.pools.create', $customer) }}" class="btn btn-success"><i class="mdi mdi-pool"></i> Add Pool</a>
    @endcan
    <a href="{{ route('customers.index') }}" class="btn btn-light">Back to list</a>
@endsection

@section('content')
    <div class="detail-layout">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="mt-0 mb-0">{{ $customer->name }}</h4>
                    <span class="badge badge-{{ $customer->is_active ? 'success' : 'secondary' }}">{{ $customer->is_active ? 'Active' : 'Turned off' }}</span>
                </div>

                <div class="form-grid">
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-phone-outline"></i> Phone</dt>
                            <dd>{{ $customer->phone ?: '—' }}</dd>
                        </dl>
                    </div>
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-email-outline"></i> Email</dt>
                            <dd>{{ $customer->email ?: '—' }}</dd>
                        </dl>
                    </div>
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-map-marker-outline"></i> Service address</dt>
                            <dd>{{ $customer->service_address }}</dd>
                        </dl>
                    </div>
                    <div class="span-12">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-key-variant"></i> Access notes</dt>
                            <dd>{{ $customer->access_notes ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0"><i class="mdi mdi-pool"></i> Pools</h4>
                    @can('manage pools')
                        <a href="{{ route('customers.pools.create', $customer) }}" class="btn btn-sm btn-primary"><i class="mdi mdi-plus"></i> Add</a>
                    @endcan
                </div>

                @forelse ($customer->pools as $pool)
                    @php
                        [$typeLabel, $typeBadge] = \App\Models\Pool::TYPES[$pool->type] ?? [$pool->type, 'secondary'];
                        $currency = $customer->company->currency ?? 'USD';
                    @endphp
                    <div class="border rounded p-2 mb-2 {{ $pool->is_active ? '' : 'bg-light' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <a href="{{ route('pools.show', $pool) }}" class="font-weight-bold">{{ $pool->displayName() }}</a>
                                <div class="mt-1">
                                    <span class="badge badge-{{ $typeBadge }}">{{ $typeLabel }}</span>
                                    <span class="badge badge-{{ $pool->is_active ? 'success' : 'secondary' }}">{{ $pool->is_active ? 'Active' : 'Off' }}</span>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    {{ $pool->gallons ? number_format($pool->gallons).' gal · ' : '' }}{{ $currency }} {{ number_format((float) $pool->monthly_rate, 2) }}/mo
                                </small>
                            </div>
                            @can('manage pools')
                                @include('partials.row-actions', ['name' => 'pools', 'model' => $pool])
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No pools yet.
                        @can('manage pools')
                            <a href="{{ route('customers.pools.create', $customer) }}">Add the first pool</a>.
                        @endcan
                    </p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
