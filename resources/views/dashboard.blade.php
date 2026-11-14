@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $currency = $stats['currency'] ?? (auth()->user()->company->currency ?? 'USD');
        $today = $stats['today'] ?? now()->toDateString();
        $weekFrom = $stats['week_start'] ?? now()->startOfWeek()->toDateString();
        $weekTo = $stats['week_end'] ?? now()->endOfWeek()->toDateString();
    @endphp

    <div class="hero-banner">
        <div>
            <div class="hero-hello">Welcome, {{ auth()->user()->name }}!</div>
            <div class="hero-text">{{ auth()->user()->company?->name }} &middot; {{ auth()->user()->role_label }} &middot; {{ now()->format('l, d F Y') }}</div>
        </div>
        <i class="mdi mdi-pool hero-art"></i>
    </div>

    @can('manage team')
        @if (($stats['technicians_active'] ?? 0) === 0)
            <div class="alert alert-warning mb-3">
                <i class="mdi mdi-account-hard-hat mr-1"></i>
                Add your technicians so they can log in.
                <a href="{{ route('team.create', ['role' => \App\Models\User::ROLE_TECHNICIAN]) }}" class="btn btn-primary btn-sm ml-2">Add Technician</a>
            </div>
        @endif
    @endcan

    <div class="row align-items-stretch">
        @isset($stats['visits_today_total'])
            <div class="col-6 col-xl-3 mb-3">
                <a href="{{ route('visits.index', ['date_from' => $today, 'date_to' => $today]) }}" class="stat-tile tile-blue">
                    <div class="value">{{ $stats['visits_today_done'] }}/{{ $stats['visits_today_total'] }}</div>
                    <div class="label">Today's visits done</div>
                    <div class="meta text-white-50">{{ $stats['visits_today_pending'] }} pending</div>
                    <i class="mdi mdi-calendar-check icon"></i>
                </a>
            </div>
            <div class="col-6 col-xl-3 mb-3">
                <a href="{{ route('visits.index', ['date_from' => \Illuminate\Support\Carbon::today()->subDays(30)->toDateString(), 'date_to' => \Illuminate\Support\Carbon::yesterday()->toDateString(), 'status' => 'scheduled']) }}" class="stat-tile tile-red">
                    <div class="value" data-count="{{ $stats['visits_overdue'] }}">{{ number_format($stats['visits_overdue']) }}</div>
                    <div class="label">Overdue visits</div>
                    <div class="meta text-white-50">{{ $stats['visits_open'] }} still open ahead</div>
                    <i class="mdi mdi-alert-circle-outline icon"></i>
                </a>
            </div>
        @endisset

        @isset($stats['customers_active'])
            <div class="col-6 col-xl-3 mb-3">
                <a href="{{ route('customers.index', ['status' => 'active']) }}" class="stat-tile tile-indigo">
                    <div class="value" data-count="{{ $stats['customers_active'] }}">{{ number_format($stats['customers_active']) }}</div>
                    <div class="label">Active customers</div>
                    <div class="meta text-white-50">{{ $stats['pools_active'] ?? 0 }} active pools</div>
                    <i class="mdi mdi-account-tie-outline icon"></i>
                </a>
            </div>
        @endisset

        @isset($stats['mrr'])
            <div class="col-6 col-xl-3 mb-3">
                <div class="stat-tile tile-green">
                    <div class="value">{{ $currency }} {{ number_format($stats['mrr'], 0) }}</div>
                    <div class="label">Monthly recurring</div>
                    <div class="meta text-white-50">From active pool rates</div>
                    <i class="mdi mdi-currency-usd icon"></i>
                </div>
            </div>
        @endisset

        @isset($stats['unpaid_invoices'])
            <div class="col-6 col-xl-3 mb-3">
                <a href="{{ route('invoices.index', ['status' => 'sent']) }}" class="stat-tile tile-amber">
                    <div class="value" data-count="{{ $stats['unpaid_invoices'] }}">{{ number_format($stats['unpaid_invoices']) }}</div>
                    <div class="label">Unpaid invoices</div>
                    <div class="meta text-white-50">{{ $currency }} {{ number_format($stats['unpaid_amount'], 2) }}</div>
                    <i class="mdi mdi-file-document-outline icon"></i>
                </a>
            </div>
        @endisset

        @isset($stats['technicians_active'])
            <div class="col-6 col-xl-3 mb-3">
                <a href="{{ route('team.index', ['role' => \App\Models\User::ROLE_TECHNICIAN, 'status' => 'active']) }}" class="stat-tile tile-blue">
                    <div class="value" data-count="{{ $stats['technicians_active'] }}">{{ number_format($stats['technicians_active']) }}</div>
                    <div class="label">Active technicians</div>
                    <div class="meta text-white-50">Ready for routes</div>
                    <i class="mdi mdi-account-hard-hat icon"></i>
                </a>
            </div>
        @endisset

        @isset($stats['visits_week_total'])
            <div class="col-6 col-xl-3 mb-3">
                <a href="{{ route('visits.index', ['date_from' => $weekFrom, 'date_to' => $weekTo]) }}" class="stat-tile tile-indigo">
                    <div class="value">{{ $stats['visits_week_done'] }}/{{ $stats['visits_week_total'] }}</div>
                    <div class="label">This week done</div>
                    <div class="meta text-white-50">{{ $stats['visits_week_pending'] }} pending</div>
                    <i class="mdi mdi-calendar-week icon"></i>
                </a>
            </div>
        @endisset

        @isset($stats['invoices_draft'])
            <div class="col-6 col-xl-3 mb-3">
                <a href="{{ route('invoices.index', ['status' => 'draft']) }}" class="stat-tile tile-green">
                    <div class="value" data-count="{{ $stats['invoices_draft'] }}">{{ number_format($stats['invoices_draft']) }}</div>
                    <div class="label">Draft invoices</div>
                    <div class="meta text-white-50">{{ number_format($stats['invoices_paid'] ?? 0) }} paid overall</div>
                    <i class="mdi mdi-file-edit-outline icon"></i>
                </a>
            </div>
        @endisset
    </div>

    <div class="row">
        @if (! empty($charts['visits_week']))
            <div class="col-xl-7 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex flex-column">
                        <h4 class="header-title"><i class="mdi mdi-chart-bar"></i> Visits this week</h4>
                        <div id="chart-visits-week" class="dash-chart flex-grow-1"></div>
                    </div>
                </div>
            </div>
        @endif

        @if (! empty($charts['invoice_status']))
            <div class="col-xl-5 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex flex-column">
                        <h4 class="header-title"><i class="mdi mdi-chart-donut"></i> Invoice status</h4>
                        <div id="chart-invoice-status" class="dash-chart dash-chart-donut flex-grow-1"></div>
                    </div>
                </div>
            </div>
        @endif

        @if (! empty($charts['tech_workload']) && count($charts['tech_workload']['labels']))
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title"><i class="mdi mdi-account-hard-hat"></i> Tech workload this week</h4>
                        <div id="chart-tech-workload" class="dash-chart"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0"><i class="mdi mdi-calendar-arrow-right"></i> Upcoming visits</h4>
                        <a href="{{ route('visits.index') }}" class="btn btn-sm btn-light">All visits</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Tech</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($upcomingVisits as $visit)
                                    <tr>
                                        <td><a href="{{ route('visits.show', $visit) }}">{{ $visit->scheduled_date->format('D, d M') }}</a></td>
                                        <td>{{ $visit->customer->name }} <span class="text-muted">· {{ $visit->pool->displayName() }}</span></td>
                                        <td>{{ $visit->technician->name }}</td>
                                        <td><span class="badge badge-{{ $visit->status_badge }}">{{ $visit->status_label }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No upcoming visits.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0"><i class="mdi mdi-history"></i> Recent activity</h4>
                        @can('view activity log')
                            <a href="{{ route('activity.index') }}" class="btn btn-sm btn-light">Full log</a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>When</th>
                                    <th>Who</th>
                                    <th>What</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentActivity as $log)
                                    <tr>
                                        <td class="text-nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                        <td>{{ $log->user?->name ?? $log->user_name ?? 'Not logged in' }}</td>
                                        <td><span class="badge badge-{{ $log->action_badge }}"><i class="mdi {{ $log->action_icon }}"></i> {{ $log->action_label }}</span> {{ $log->subject_label }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No activity yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
(function () {
    var charts = @json($charts);
    var ink = '#0f2a3d';
    var muted = '#7b8fa1';

    if (charts.visits_week && document.querySelector('#chart-visits-week')) {
        new ApexCharts(document.querySelector('#chart-visits-week'), {
            chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [
                { name: 'Completed', data: charts.visits_week.completed },
                { name: 'Pending', data: charts.visits_week.pending },
                { name: 'Skipped', data: charts.visits_week.skipped }
            ],
            colors: ['#10b981', '#0284c7', '#94a3b8'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '48%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: charts.visits_week.labels, labels: { style: { colors: muted } } },
            yaxis: { labels: { style: { colors: muted } }, forceNiceScale: true, min: 0 },
            grid: { borderColor: '#e8f1f5', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', labels: { colors: ink } },
            tooltip: { theme: 'light' }
        }).render();
    }

    if (charts.invoice_status && document.querySelector('#chart-invoice-status')) {
        var invoiceTotal = charts.invoice_status.values.reduce(function (a, b) { return a + b; }, 0);
        new ApexCharts(document.querySelector('#chart-invoice-status'), {
            chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
            series: charts.invoice_status.values,
            labels: charts.invoice_status.labels,
            colors: ['#94a3b8', '#f59e0b', '#10b981'],
            legend: { position: 'bottom', labels: { colors: ink } },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            name: { color: muted },
                            value: { fontSize: '22px', fontWeight: 800, color: ink },
                            total: { show: true, label: 'Invoices', color: muted, formatter: function () { return invoiceTotal; } }
                        }
                    }
                }
            }
        }).render();
    }

    if (charts.tech_workload && charts.tech_workload.labels.length && document.querySelector('#chart-tech-workload')) {
        new ApexCharts(document.querySelector('#chart-tech-workload'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Visits', data: charts.tech_workload.values }],
            colors: ['#0891b2'],
            plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '58%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: charts.tech_workload.labels, labels: { style: { colors: muted } } },
            yaxis: { labels: { style: { colors: ink, fontWeight: 600 } } },
            grid: { borderColor: '#e8f1f5', strokeDashArray: 4 },
            tooltip: { theme: 'light' }
        }).render();
    }
})();
</script>
@endpush
