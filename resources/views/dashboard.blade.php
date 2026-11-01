@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="hero-banner">
        <div>
            <div class="hero-hello">Welcome, {{ auth()->user()->name }}!</div>
            <div class="hero-text">{{ now()->format('l, d F Y') }}</div>
        </div>
        <i class="mdi mdi-pool hero-art"></i>
    </div>

    <div class="row">
        @foreach ([
            ['Users', $stats['users'], 'ri-team-line', 'indigo'],
            ['Activity Today', $stats['activity_today'], 'ri-pulse-line', 'blue'],
        ] as [$label, $value, $icon, $color])
            <div class="col-md-6 col-xl-4">
                <div class="stat-tile tile-{{ $color }}">
                    <div class="value" data-count="{{ $value }}">{{ number_format($value) }}</div>
                    <div class="label">{{ $label }}</div>
                    <i class="{{ $icon }} icon"></i>
                </div>
            </div>
        @endforeach
    </div>

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
@endsection
