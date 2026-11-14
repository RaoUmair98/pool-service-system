@extends('layouts.admin')

@section('body_class', 'list-layout')
@section('title', 'Email log')


@section('content')
    <div class="card list-page-card" data-infinite-root>
        <div class="card-body list-page-body">
            <div class="list-page-toolbar">
                <form method="GET" class="filters-bar">
                    <div class="fg-search">
                        <label class="mb-1"><i class="mdi mdi-magnify"></i> Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Email, subject or customer">
                    </div>
                    <div class="fg-field">
                        <label class="mb-1">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All types</option>
                            @foreach (\App\Models\EmailLog::TYPES as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-field">
                        <label class="mb-1">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            @foreach (\App\Models\EmailLog::STATUSES as $value => [$label])
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg-actions">
                        <a href="{{ route('email-logs.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-scroll" data-infinite-scroll>
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>When</th>
                            <th>Type</th>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody data-infinite-body>
                        @include('email-logs.rows')
                        @if ($logs->isEmpty())
                            <tr data-infinite-empty>
                                <td colspan="6" class="text-center">No emails logged yet.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @include('partials.infinite-scroll', ['paginator' => $logs])
            </div>
        </div>
    </div>
@endsection
