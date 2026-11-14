@extends('layouts.admin')

@section('body_class', 'list-layout')
@section('title', 'Invoices')


@section('actions')
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#generate-invoices-modal">
        <i class="mdi mdi-file-plus-outline"></i> Generate invoices
    </button>
@endsection

@section('content')
    <div class="card list-page-card" data-infinite-root>
        <div class="card-body list-page-body">
            <div class="list-page-toolbar">
                <form method="GET" class="filters-bar">
                    <div class="fg-search">
                        <label class="mb-1"><i class="mdi mdi-magnify"></i> Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Invoice # or customer">
                    </div>
                    <div class="fg-field">
                        <label class="mb-1">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            @foreach (\App\Models\Invoice::STATUSES as $value => [$label])
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-field">
                        <label class="mb-1">Customer</label>
                        <select name="customer_id" class="form-control">
                            <option value="">All</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-field-sm">
                        <label class="mb-1">Year</label>
                        <input type="number" name="period_year" value="{{ request('period_year') }}" class="form-control" min="2020" max="2100" placeholder="YYYY">
                    </div>
                    <div class="fg-field-sm">
                        <label class="mb-1">Month</label>
                        <input type="number" name="period_month" value="{{ request('period_month') }}" class="form-control" min="1" max="12" placeholder="M">
                    </div>
                    <div class="fg-actions">
                        <a href="{{ route('invoices.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-scroll" data-infinite-scroll>
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Period</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody data-infinite-body>
                        @include('invoices.rows')
                        @if ($invoices->isEmpty())
                            <tr data-infinite-empty>
                                <td colspan="6" class="text-center">No invoices yet. Click Generate invoices to create drafts for the month.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @include('partials.infinite-scroll', ['paginator' => $invoices])
            </div>
        </div>
    </div>

    <div class="modal fade" id="generate-invoices-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('invoices.generate') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate monthly invoices</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Creates one draft invoice per active customer with active pool rates. Safe to rerun — existing periods are skipped.</p>
                    <div class="form-row">
                        <div class="col-6">
                            <label for="year">Year</label>
                            <input type="number" id="year" name="year" class="form-control" value="{{ now()->year }}" min="2020" max="2100" required>
                        </div>
                        <div class="col-6">
                            <label for="month">Month</label>
                            <input type="number" id="month" name="month" class="form-control" value="{{ now()->month }}" min="1" max="12" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Generate</button>
                </div>
            </form>
        </div>
    </div>
@endsection
