@extends('layouts.admin')

@section('title', $member->exists ? 'Edit Team Member' : 'Add '.(\App\Models\User::ROLES[$role][0] ?? 'Team Member'))

@section('content')
    @php $isSelf = $member->exists && $member->is(auth()->user()); @endphp

    <div class="form-shell">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ $member->exists ? route('team.update', $member) : route('team.store') }}">
                    @csrf
                    @if ($member->exists)
                        @method('PUT')
                    @endif

                    <div class="form-grid">
                        <div class="span-3">
                            <x-ui.field name="role" label="Role" required icon="mdi-shield-account-outline"
                                :hint="$isSelf ? 'You cannot change your own role.' : null">
                                <select id="role" name="role" class="form-control" required @disabled($isSelf)>
                                    @foreach (\App\Models\User::ROLES as $value => [$label])
                                        <option value="{{ $value }}" @selected(old('role', $role) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if ($isSelf)
                                    <input type="hidden" name="role" value="{{ $role }}">
                                @endif
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="name" label="Full Name" required>
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $member->name) }}" required autofocus>
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="email" label="Email" required hint="Login email.">
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $member->email) }}" required>
                            </x-ui.field>
                        </div>
                        <div class="span-3">
                            <x-ui.field name="phone" label="Phone">
                                <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
                            </x-ui.field>
                        </div>
                        <div class="span-6">
                            <x-ui.field name="password" label="Password" :required="! $member->exists" icon="mdi-lock-outline"
                                :hint="$member->exists ? 'Leave empty to keep the current password.' : 'Share this so they can log in.'">
                                <input type="text" id="password" name="password" class="form-control" autocomplete="new-password" @required(! $member->exists)>
                            </x-ui.field>
                        </div>
                    </div>

                    @include('partials.form-actions', [
                        'model' => $member,
                        'cancel' => route('team.index'),
                        'saveAndNew' => true,
                        'lockActive' => $isSelf,
                        'activeHint' => $isSelf ? 'You cannot turn off your own account.' : 'Turned off members cannot log in.',
                    ])
                </form>
            </div>
        </div>
    </div>
@endsection
