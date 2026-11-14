@extends('layouts.admin')

@section('body_class', 'list-layout')
@section('title', 'Visits')


@section('actions')
    <form method="POST" action="{{ route('visits.generate') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success"><i class="mdi mdi-calendar-plus"></i> Generate visits</button>
    </form>
@endsection

@section('content')
    <div class="card list-page-card" data-infinite-root>
        <div class="card-body list-page-body">
            <div class="list-page-toolbar">
                <form method="GET" class="filters-bar">
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-calendar-start"></i> From</label>
                        <x-ui.date name="date_from" :value="request('date_from', $dateFrom)" />
                    </div>
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-calendar-end"></i> To</label>
                        <x-ui.date name="date_to" :value="request('date_to', $dateTo)" />
                    </div>
                    <div class="fg-search">
                        <label class="mb-1"><i class="mdi mdi-account-hard-hat"></i> Technician</label>
                        <select name="technician_user_id" class="form-control">
                            <option value="">All technicians</option>
                            @foreach ($technicians as $tech)
                                <option value="{{ $tech->id }}" @selected((string) request('technician_user_id') === (string) $tech->id)>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-flag-outline"></i> Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            @foreach (\App\Models\Visit::STATUSES as $value => [$label])
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-actions">
                        <a href="{{ route('visits.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-scroll" data-infinite-scroll>
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th><i class="mdi mdi-calendar"></i> Date</th>
                            <th><i class="mdi mdi-account-outline"></i> Customer</th>
                            <th><i class="mdi mdi-pool"></i> Pool</th>
                            <th><i class="mdi mdi-account-hard-hat"></i> Technician</th>
                            <th><i class="mdi mdi-flag-outline"></i> Status</th>
                            <th><i class="mdi mdi-cog-outline"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody data-infinite-body>
                        @include('visits.rows')
                        @if ($visits->isEmpty())
                            <tr data-infinite-empty>
                                <td colspan="6" class="text-center">No visits in this range. Set a pool schedule, then click Generate visits.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @include('partials.infinite-scroll', ['paginator' => $visits])
            </div>
        </div>
    </div>
@endsection
