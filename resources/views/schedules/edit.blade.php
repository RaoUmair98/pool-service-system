@extends('layouts.admin')

@section('title', 'Schedule — '.$pool->displayName())


@section('content')
    <div class="form-shell">
        <div class="card">
            <div class="card-body">
                @if ($technicians->isEmpty())
                    <div class="alert alert-warning mb-3">
                        Add an active technician on the <a href="{{ route('team.create', ['role' => \App\Models\User::ROLE_TECHNICIAN]) }}">Team</a> page before setting a schedule.
                    </div>
                @endif

                <form method="POST" action="{{ route('pools.schedule.update', $pool) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="span-4">
                            <x-ui.field name="weekday" label="Service day" required icon="mdi-calendar">
                                <select id="weekday" name="weekday" class="form-control" required>
                                    @foreach (\App\Models\ServiceSchedule::WEEKDAYS as $value => $label)
                                        <option value="{{ $value }}" @selected((int) old('weekday', $schedule->weekday) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                        <div class="span-8">
                            <x-ui.field name="technician_user_id" label="Technician" required icon="mdi-account-hard-hat">
                                <select id="technician_user_id" name="technician_user_id" class="form-control select2" required @disabled($technicians->isEmpty())>
                                    <option value="">Select technician</option>
                                    @foreach ($technicians as $tech)
                                        <option value="{{ $tech->id }}" @selected((int) old('technician_user_id', $schedule->technician_user_id) === $tech->id)>{{ $tech->name }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                    </div>

                    @include('partials.form-actions', [
                        'model' => $schedule,
                        'cancel' => route('pools.show', $pool),
                        'activeHint' => 'Turn the schedule off to stop generating new visits.',
                    ])
                </form>
            </div>
        </div>
    </div>
@endsection
