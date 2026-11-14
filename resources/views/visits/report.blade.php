@extends('layouts.admin')

@section('title', 'Service report')

@section('actions')
    <form method="POST" action="{{ route('visits.report.resend', $visit) }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary"><i class="mdi mdi-email-fast-outline"></i> Resend email</button>
    </form>
    <a href="{{ route('visits.show', $visit) }}" class="btn btn-light">Back to visit</a>
@endsection


@section('content')
    <div class="form-shell">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 mb-1">Service report</h4>
                <p class="text-muted mb-3">
                    {{ $visit->customer->name }} · {{ $visit->pool->displayName() }} · {{ $visit->scheduled_date->format('l, d M Y') }}
                    · To: {{ $visit->customer->email ?: '— (no email)' }}
                    · Tech: {{ $visit->technician->name }}
                </p>

                <div class="form-grid">
                    <div class="span-6">
                        <div class="section-label">Checklist</div>
                        <ul class="mb-0 pl-3">
                            @foreach (\App\Models\Visit::CHECKLIST_ITEMS as $key => $label)
                                <li>{{ ($checklist[$key] ?? false) ? '✓' : '○' }} {{ $label }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="span-6">
                        <div class="section-label">Water readings</div>
                        <p class="mb-2">
                            pH {{ $visit->reading_ph ?? '—' }}
                            · Free chlorine {{ $visit->reading_free_chlorine ?? '—' }}
                            · Alkalinity {{ $visit->reading_alkalinity ?? '—' }}
                        </p>
                        @if ($visit->notes)
                            <div class="section-label">Notes</div>
                            <p class="mb-0">{{ $visit->notes }}</p>
                        @endif
                    </div>
                    @if ($visit->photos->isNotEmpty())
                        <div class="span-12">
                            <div class="section-label">Photos</div>
                            <div class="d-flex flex-wrap" style="gap: .65rem;">
                                @foreach ($visit->photos as $photo)
                                    <div>
                                        <a href="{{ $photo->url() }}" target="_blank" rel="noopener">
                                            <img src="{{ $photo->url() }}" alt="{{ $photo->kind }}" class="border rounded" style="height: 90px; width: 120px; object-fit: cover;">
                                        </a>
                                        <div class="small text-muted">{{ \App\Models\VisitPhoto::KINDS[$photo->kind] ?? $photo->kind }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
