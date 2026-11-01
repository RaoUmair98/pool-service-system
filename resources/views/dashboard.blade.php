@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="hero-banner">
        <div>
            <div class="hero-hello">Welcome, {{ auth()->user()->name }}!</div>
            <div class="hero-text">{{ auth()->user()->company?->name }} &middot; {{ auth()->user()->role_label }} &middot; {{ now()->format('l, d F Y') }}</div>
        </div>
        <i class="mdi mdi-pool hero-art"></i>
    </div>

    @can('manage team')
        @if ($stats['technicians'] === 0)
            <div class="alert help-box">
                <i class="mdi mdi-lightbulb-on-outline mr-1"></i>
                Add your technicians so they can log in.
                <a href="{{ route('team.create', ['role' => \App\Models\User::ROLE_TECHNICIAN]) }}" class="btn btn-primary btn-sm ml-2"><i class="mdi mdi-account-hard-hat"></i> Add Technician</a>
            </div>
        @endif

        <div class="row">
            @foreach ([
                ['Technicians', $stats['technicians'], 'ri-tools-line', 'blue', route('team.index', ['role' => \App\Models\User::ROLE_TECHNICIAN])],
                ['Admins', $stats['admins'], 'ri-shield-user-line', 'indigo', route('team.index', ['role' => \App\Models\User::ROLE_ADMIN])],
                ['Activity Today', $stats['activity_today'], 'ri-history-line', 'green', auth()->user()->can('view activity log') ? route('activity.index', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) : null],
            ] as [$label, $value, $icon, $color, $link])
                <div class="col-md-6 col-xl-4">
                    @if ($link)
                        <a href="{{ $link }}" class="stat-tile tile-{{ $color }}">
                    @else
                        <div class="stat-tile tile-{{ $color }}">
                    @endif
                        <div class="value" data-count="{{ $value }}">{{ number_format($value) }}</div>
                        <div class="label">{{ $label }}</div>
                        <i class="{{ $icon }} icon"></i>
                    @if ($link)
                        </a>
                    @else
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endcan

    @can('view activity log')
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3"><i class="mdi mdi-history"></i> Recent Activity</h4>
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
    @endcan
@endsection
