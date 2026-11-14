@extends('layouts.tech')

@section('title', "Today's Route")

@section('content')
    <div class="tech-heading">
        <h1>Today's Route</h1>
        <p>{{ \Carbon\Carbon::parse($today)->format('l, d M Y') }} · {{ $remaining }} left · {{ $done }} done</p>
    </div>

    @forelse ($visits as $visit)
        <a href="{{ route('route.show', $visit) }}" class="tech-stop status-{{ $visit->status }}">
            <div class="tech-stop-status">
                <span class="badge badge-{{ $visit->status_badge }}">{{ $visit->status_label }}</span>
            </div>
            <div class="tech-stop-body">
                <div class="tech-stop-name">{{ $visit->customer->name }}</div>
                <div class="tech-stop-pool"><i class="mdi mdi-pool"></i> {{ $visit->pool->displayName() }} · {{ $visit->pool->type_label }}</div>
                <div class="tech-stop-address"><i class="mdi mdi-map-marker-outline"></i> {{ $visit->customer->service_address }}</div>
            </div>
            <i class="mdi mdi-chevron-right tech-stop-chevron"></i>
        </a>
    @empty
        <div class="tech-empty">
            <i class="mdi mdi-calendar-blank"></i>
            <p>No stops scheduled for you today.</p>
        </div>
    @endforelse
@endsection
