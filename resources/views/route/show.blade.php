@extends('layouts.tech')

@section('title', $visit->customer->name)

@section('content')
    <div class="tech-back">
        <a href="{{ route('route.index') }}"><i class="mdi mdi-arrow-left"></i> Today's Route</a>
    </div>

    <div class="tech-heading">
        <h1>{{ $visit->customer->name }}</h1>
        <p>
            <span class="badge badge-{{ $visit->status_badge }}"><i class="mdi {{ $visit->status_icon }}"></i> {{ $visit->status_label }}</span>
            {{ $visit->pool->displayName() }} · {{ $visit->pool->type_label }}
        </p>
    </div>

    <div class="tech-card">
        <div class="tech-card-row"><i class="mdi mdi-map-marker-outline"></i> {{ $visit->customer->service_address }}</div>
        @if ($visit->customer->access_notes)
            <div class="tech-card-row tech-access"><i class="mdi mdi-key-variant"></i> {{ $visit->customer->access_notes }}</div>
        @endif
        @if ($visit->pool->gallons)
            <div class="tech-card-row"><i class="mdi mdi-cup-water"></i> {{ number_format($visit->pool->gallons) }} gallons</div>
        @endif
        @if ($visit->pool->equipment->isNotEmpty())
            <div class="tech-card-row">
                <i class="mdi mdi-wrench-outline"></i>
                {{ $visit->pool->equipment->pluck('name')->join(', ') }}
            </div>
        @endif
    </div>

    @if ($lastVisit)
        <div class="tech-card">
            <div class="tech-card-title">Last readings ({{ $lastVisit->completed_at?->format('d M Y') }})</div>
            <div class="tech-readings-prev">
                <span>pH {{ $lastVisit->reading_ph ?? '—' }}</span>
                <span>Cl {{ $lastVisit->reading_free_chlorine ?? '—' }}</span>
                <span>Alk {{ $lastVisit->reading_alkalinity ?? '—' }}</span>
            </div>
        </div>
    @endif

    @if ($visit->status === \App\Models\Visit::STATUS_SCHEDULED)
        <form method="POST" action="{{ route('route.start', $visit) }}" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-primary btn-lg btn-block tech-btn-lg">
                <i class="mdi mdi-play-circle-outline"></i> Start visit
            </button>
        </form>
    @endif

    @if ($visit->isOpen())
        <form method="POST" action="{{ route('route.complete', $visit) }}" id="visit-form" class="tech-visit-form">
            @csrf

            <div class="tech-card">
                <div class="tech-card-title">Checklist</div>
                @foreach (\App\Models\Visit::CHECKLIST_ITEMS as $key => $label)
                    <label class="tech-check">
                        <input type="checkbox" name="checklist[{{ $key }}]" value="1" @checked(old("checklist.$key", $checklist[$key] ?? false))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="tech-card">
                <div class="tech-card-title">Water readings</div>
                <div class="form-row">
                    <div class="col-4">
                        <label for="reading_ph">pH</label>
                        <input type="number" step="0.01" min="0" max="14" id="reading_ph" name="reading_ph"
                               class="form-control form-control-lg" value="{{ old('reading_ph', $visit->reading_ph) }}" inputmode="decimal">
                    </div>
                    <div class="col-4">
                        <label for="reading_free_chlorine">Chlorine</label>
                        <input type="number" step="0.01" min="0" max="50" id="reading_free_chlorine" name="reading_free_chlorine"
                               class="form-control form-control-lg" value="{{ old('reading_free_chlorine', $visit->reading_free_chlorine) }}" inputmode="decimal">
                    </div>
                    <div class="col-4">
                        <label for="reading_alkalinity">Alkalinity</label>
                        <input type="number" step="0.1" min="0" max="500" id="reading_alkalinity" name="reading_alkalinity"
                               class="form-control form-control-lg" value="{{ old('reading_alkalinity', $visit->reading_alkalinity) }}" inputmode="decimal">
                    </div>
                </div>
            </div>

            <div class="tech-card">
                <div class="tech-card-title">Notes / chemicals used</div>
                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Chemicals added, issues, etc.">{{ old('notes', $visit->notes) }}</textarea>
            </div>

            <div class="tech-sticky-actions">
                <button type="submit" formaction="{{ route('route.update', $visit) }}" class="btn btn-light btn-lg">
                    <i class="mdi mdi-content-save-outline"></i> Save
                </button>
                <button type="submit" class="btn btn-success btn-lg" data-confirm="Mark this visit complete?">
                    <i class="mdi mdi-check-circle-outline"></i> Complete
                </button>
            </div>
        </form>

        <div class="tech-card">
            <div class="tech-card-title">Photos</div>
            @if ($visit->photos->isNotEmpty())
                <div class="tech-photos">
                    @foreach ($visit->photos as $photo)
                        <div class="tech-photo">
                            <img src="{{ $photo->url() }}" alt="{{ $photo->kind }}">
                            <div class="tech-photo-meta">
                                {{ \App\Models\VisitPhoto::KINDS[$photo->kind] ?? $photo->kind }}
                                <form method="POST" action="{{ route('route.photos.destroy', [$visit, $photo]) }}" class="d-inline"
                                      data-confirm="Remove this photo?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('route.photos.store', $visit) }}" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div class="form-row align-items-end">
                    <div class="col-5 mb-2">
                        <label for="kind">Type</label>
                        <select name="kind" id="kind" class="form-control" required>
                            @foreach (\App\Models\VisitPhoto::KINDS as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'before')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7 mb-2">
                        <label for="photo">Photo</label>
                        <input type="file" name="photo" id="photo" class="form-control-file" accept="image/*" capture="environment" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary btn-block"><i class="mdi mdi-camera"></i> Add photo</button>
            </form>
        </div>
    @elseif ($visit->status === \App\Models\Visit::STATUS_COMPLETED)
        <div class="tech-card">
            <div class="tech-card-title">Checklist</div>
            <ul class="mb-0 pl-3">
                @foreach (\App\Models\Visit::CHECKLIST_ITEMS as $key => $label)
                    <li>{{ ($checklist[$key] ?? false) ? '✓' : '○' }} {{ $label }}</li>
                @endforeach
            </ul>
        </div>
        <div class="tech-card">
            <div class="tech-card-title">Readings</div>
            <div class="tech-readings-prev">
                <span>pH {{ $visit->reading_ph ?? '—' }}</span>
                <span>Cl {{ $visit->reading_free_chlorine ?? '—' }}</span>
                <span>Alk {{ $visit->reading_alkalinity ?? '—' }}</span>
            </div>
            @if ($visit->notes)
                <p class="mt-2 mb-0">{{ $visit->notes }}</p>
            @endif
        </div>
        @if ($visit->photos->isNotEmpty())
            <div class="tech-card">
                <div class="tech-card-title">Photos</div>
                <div class="tech-photos">
                    @foreach ($visit->photos as $photo)
                        <div class="tech-photo">
                            <img src="{{ $photo->url() }}" alt="{{ $photo->kind }}">
                            <div class="tech-photo-meta">{{ \App\Models\VisitPhoto::KINDS[$photo->kind] ?? $photo->kind }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
        if (!confirm(el.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
