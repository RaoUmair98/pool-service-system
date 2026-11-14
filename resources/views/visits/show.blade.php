@extends('layouts.admin')

@section('title', 'Visit — '.$visit->scheduled_date->format('d M Y'))

@section('actions')
    @if ($visit->status === \App\Models\Visit::STATUS_COMPLETED)
        @can('view reports')
            <a href="{{ route('visits.report', $visit) }}" class="btn btn-primary"><i class="mdi mdi-file-document-outline"></i> Service report</a>
            <form method="POST" action="{{ route('visits.report.resend', $visit) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success"><i class="mdi mdi-email-fast-outline"></i> Resend report</button>
            </form>
        @endcan
    @endif
    <a href="{{ route('visits.index') }}" class="btn btn-light">Back to visits</a>
@endsection

@section('content')
    <div class="detail-layout">
        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge badge-{{ $visit->status_badge }}"><i class="mdi {{ $visit->status_icon }}"></i> {{ $visit->status_label }}</span>
                </div>

                <div class="form-grid">
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-calendar"></i> Date</dt>
                            <dd>{{ $visit->scheduled_date->format('l, d M Y') }}</dd>
                        </dl>
                    </div>
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-account-hard-hat"></i> Technician</dt>
                            <dd>{{ $visit->technician->name }}</dd>
                        </dl>
                    </div>
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-pool"></i> Pool</dt>
                            <dd>
                                <a href="{{ route('pools.show', $visit->pool) }}">{{ $visit->pool->displayName() }}</a>
                                · {{ $visit->pool->type_label }}
                                @if ($visit->pool->gallons)
                                    · {{ number_format($visit->pool->gallons) }} gal
                                @endif
                            </dd>
                        </dl>
                    </div>
                    <div class="span-12">
                        <dl class="detail-dl">
                            <dt><i class="mdi mdi-account-outline"></i> Customer</dt>
                            <dd>
                                <a href="{{ route('customers.show', $visit->customer) }}">{{ $visit->customer->name }}</a>
                                — {{ $visit->customer->service_address }}
                                @if ($visit->customer->access_notes)
                                    <div class="mt-1"><i class="mdi mdi-key-variant"></i> {{ $visit->customer->access_notes }}</div>
                                @endif
                            </dd>
                        </dl>
                    </div>

                    @if ($visit->status === \App\Models\Visit::STATUS_SKIPPED)
                        <div class="span-12">
                            <dl class="detail-dl">
                                <dt><i class="mdi mdi-cancel"></i> Skip reason</dt>
                                <dd>{{ $visit->skip_reason }}</dd>
                            </dl>
                        </div>
                    @endif

                    @if ($visit->status === \App\Models\Visit::STATUS_COMPLETED)
                        <div class="span-4">
                            <dl class="detail-dl">
                                <dt><i class="mdi mdi-clock-outline"></i> Times</dt>
                                <dd>
                                    Started {{ $visit->started_at?->format('h:i A') ?? '—' }}
                                    · Completed {{ $visit->completed_at?->format('h:i A') ?? '—' }}
                                </dd>
                            </dl>
                        </div>
                        <div class="span-8">
                            <dl class="detail-dl">
                                <dt><i class="mdi mdi-checkbox-marked-outline"></i> Checklist</dt>
                                <dd>
                                    @foreach (\App\Models\Visit::CHECKLIST_ITEMS as $key => $label)
                                        <span class="badge badge-{{ ($visit->checklistItems()[$key] ?? false) ? 'success' : 'light' }} mr-1">{{ $label }}</span>
                                    @endforeach
                                </dd>
                            </dl>
                        </div>
                        <div class="span-6">
                            <dl class="detail-dl">
                                <dt><i class="mdi mdi-flask-outline"></i> Readings</dt>
                                <dd>
                                    pH {{ $visit->reading_ph ?? '—' }}
                                    · Cl {{ $visit->reading_free_chlorine ?? '—' }}
                                    · Alk {{ $visit->reading_alkalinity ?? '—' }}
                                </dd>
                            </dl>
                        </div>
                        @if ($visit->notes)
                            <div class="span-6">
                                <dl class="detail-dl">
                                    <dt><i class="mdi mdi-note-text-outline"></i> Notes</dt>
                                    <dd>{{ $visit->notes }}</dd>
                                </dl>
                            </div>
                        @endif
                    @endif
                </div>

                @if ($visit->photos->isNotEmpty())
                    <div class="section-divider"></div>
                    <div class="section-label"><i class="mdi mdi-camera"></i> Photos</div>
                    <div class="d-flex flex-wrap" style="gap: .65rem;">
                        @foreach ($visit->photos as $photo)
                            <div>
                                <img src="{{ $photo->url() }}" alt="{{ $photo->kind }}" class="border rounded" style="height: 90px; width: 120px; object-fit: cover;">
                                <div class="small text-muted">{{ \App\Models\VisitPhoto::KINDS[$photo->kind] ?? $photo->kind }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div>
            @if ($visit->isOpen())
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title"><i class="mdi mdi-account-switch"></i> Reassign</h4>
                        <form method="POST" action="{{ route('visits.reassign', $visit) }}">
                            @csrf
                            <x-ui.field name="technician_user_id" label="Technician" required>
                                <select id="technician_user_id" name="technician_user_id" class="form-control" required>
                                    @foreach ($technicians as $tech)
                                        <option value="{{ $tech->id }}" @selected($visit->technician_user_id === $tech->id)>{{ $tech->name }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                            <button type="submit" class="btn btn-primary btn-sm">Reassign</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title"><i class="mdi mdi-calendar-arrow-right"></i> Reschedule</h4>
                        <form method="POST" action="{{ route('visits.reschedule', $visit) }}">
                            @csrf
                            <x-ui.field name="scheduled_date" label="New date" required>
                                <x-ui.date name="scheduled_date" :value="old('scheduled_date', $visit->scheduled_date->toDateString())" required />
                            </x-ui.field>
                            <button type="submit" class="btn btn-primary btn-sm">Move visit</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title"><i class="mdi mdi-cancel"></i> Skip visit</h4>
                        <form method="POST" action="{{ route('visits.skip', $visit) }}"
                              data-confirm="Skip this visit? It will not be done on this date.">
                            @csrf
                            <x-ui.field name="skip_reason" label="Reason" required>
                                <textarea id="skip_reason" name="skip_reason" class="form-control" rows="2" required>{{ old('skip_reason') }}</textarea>
                            </x-ui.field>
                            <button type="submit" class="btn btn-soft-danger btn-sm">Skip visit</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-muted">
                        This visit is {{ strtolower($visit->status_label) }} and can no longer be reassigned, moved, or skipped.
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
