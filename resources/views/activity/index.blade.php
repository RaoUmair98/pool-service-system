@extends('layouts.admin')

@section('title', 'Activity Log')

@section('help')
    Everything anyone does is written here by itself: who did it, what, when, from where and why.
    Nothing in this list can be changed or deleted.
@endsection

@section('actions')
    <button type="button" class="btn btn-light" onclick="window.print()"><i class="mdi mdi-printer"></i> Print</button>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="d-print-none mb-3">
                <div class="form-row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="mb-1"><i class="mdi mdi-magnify"></i> Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Record, reason, user...">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="mb-1"><i class="mdi mdi-shape-outline"></i> Kind</label>
                        <select name="group" class="form-control">
                            <option value="">Everything</option>
                            @foreach (\App\Models\ActivityLog::GROUPS as $value => $label)
                                <option value="{{ $value }}" @selected(request('group') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="mb-1"><i class="mdi mdi-swap-vertical"></i> Action</label>
                        <select name="action" class="form-control">
                            <option value="">All actions</option>
                            @foreach (\App\Models\ActivityLog::ACTIONS as $value => $action)
                                <option value="{{ $value }}" @selected(request('action') === $value)>{{ $action[0] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="mb-1"><i class="mdi mdi-account-outline"></i> User</label>
                        <select name="user_id" class="form-control">
                            <option value="">All users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="mb-1"><i class="mdi mdi-calendar"></i> From / To</label>
                        <div class="d-flex">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control mr-1">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
                <a href="{{ request()->fullUrlWithQuery(['date_from' => today()->toDateString(), 'date_to' => today()->toDateString(), 'page' => null]) }}" class="btn btn-light">Today only</a>
            </form>

            <div class="table-responsive">
                <table class="table table-centered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th><i class="mdi mdi-calendar"></i> When</th>
                            <th><i class="mdi mdi-account-outline"></i> Who</th>
                            <th><i class="mdi mdi-swap-vertical"></i> What</th>
                            <th><i class="mdi mdi-cube-outline"></i> On what</th>
                            <th><i class="mdi mdi-text"></i> Details</th>
                            <th><i class="mdi mdi-note-text-outline"></i> Why</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-nowrap">
                                    <strong>{{ $log->created_at->format('d M Y') }}</strong><br>
                                    <span class="text-muted">{{ $log->created_at->format('h:i:s A') }}</span>
                                </td>
                                <td class="text-nowrap">
                                    {{-- The person's current name; the saved one is only for accounts that no longer exist. --}}
                                    {{ $log->user?->name ?? $log->user_name ?? 'Not logged in' }}
                                    @if ($log->ip_address)
                                        <br><small class="text-muted">{{ $log->ip_address }}</small>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <span class="badge badge-{{ $log->action_badge }}"><i class="mdi {{ $log->action_icon }}"></i> {{ $log->action_label }}</span>
                                </td>
                                <td class="name-cell">{{ $log->subject_label ?? '-' }}</td>
                                <td>
                                    @forelse ($log->changes ?? [] as $field => $change)
                                        <div class="log-change">
                                            <span class="log-field">{{ $field }}:</span>
                                            @if (($change['old'] ?? null) !== null)
                                                <span class="log-old">{{ $change['old'] }}</span>
                                                <i class="mdi mdi-arrow-right"></i>
                                            @endif
                                            <span class="log-new">{{ $change['new'] ?? 'empty' }}</span>
                                        </div>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td class="text-wrap">{{ $log->reason ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No activity found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.pagination', ['paginator' => $logs])
        </div>
    </div>
@endsection
