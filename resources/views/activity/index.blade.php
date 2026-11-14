@extends('layouts.admin')

@section('body_class', 'list-layout')
@section('title', 'Activity Log')

@section('content')
    <div class="card list-page-card" data-infinite-root>
        <div class="card-body list-page-body">
            <div class="list-page-toolbar">
                <form method="GET" class="filters-bar d-print-none">
                    <div class="fg-search">
                        <label class="mb-1"><i class="mdi mdi-magnify"></i> Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Record, reason, user...">
                    </div>
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-shape-outline"></i> Kind</label>
                        <select name="group" class="form-control">
                            <option value="">Everything</option>
                            @foreach (\App\Models\ActivityLog::GROUPS as $value => $label)
                                <option value="{{ $value }}" @selected(request('group') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-swap-vertical"></i> Action</label>
                        <select name="action" class="form-control">
                            <option value="">All actions</option>
                            @foreach (\App\Models\ActivityLog::ACTIONS as $value => $action)
                                <option value="{{ $value }}" @selected(request('action') === $value)>{{ $action[0] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-account-outline"></i> User</label>
                        <select name="user_id" class="form-control">
                            <option value="">All users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-calendar"></i> From</label>
                        <x-ui.date name="date_from" :value="request('date_from')" />
                    </div>
                    <div class="fg-field">
                        <label class="mb-1"><i class="mdi mdi-calendar"></i> To</label>
                        <x-ui.date name="date_to" :value="request('date_to')" />
                    </div>
                    <div class="fg-actions">
                        <a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => today()->toDateString(), 'date_to' => today()->toDateString(), 'page' => null]) }}" class="btn btn-light">Today only</a>
                    </div>
                </form>
            </div>

            <div class="table-scroll" data-infinite-scroll>
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
                    <tbody data-infinite-body>
                        @include('activity.rows')
                        @if ($logs->isEmpty())
                            <tr data-infinite-empty>
                                <td colspan="6" class="text-center">No activity found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @include('partials.infinite-scroll', ['paginator' => $logs])
            </div>
        </div>
    </div>
@endsection
