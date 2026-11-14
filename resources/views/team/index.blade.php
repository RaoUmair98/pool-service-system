@extends('layouts.admin')

@section('body_class', 'list-layout')
@section('title', 'Team')


@section('actions')
    <a href="{{ route('team.create', ['role' => \App\Models\User::ROLE_TECHNICIAN]) }}" class="btn btn-primary"><i class="mdi mdi-account-hard-hat"></i> Add Technician</a>
    <a href="{{ route('team.create', ['role' => \App\Models\User::ROLE_ADMIN]) }}" class="btn btn-light"><i class="mdi mdi-shield-account-outline"></i> Add Admin</a>
@endsection

@section('content')
    <div class="card list-page-card" data-infinite-root>
        <div class="card-body list-page-body">
            <div class="list-page-toolbar">
                @include('partials.master-filters', [
                    'placeholder' => 'Search by name, email or phone',
                    'roles' => array_map(fn ($role) => $role[0], \App\Models\User::ROLES),
                ])
            </div>

            <div class="table-scroll" data-infinite-scroll>
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th><i class="mdi mdi-tag-outline"></i> Name</th>
                            <th><i class="mdi mdi-shield-account-outline"></i> Role</th>
                            <th><i class="mdi mdi-email-outline"></i> Email</th>
                            <th><i class="mdi mdi-phone-outline"></i> Phone</th>
                            <th><i class="mdi mdi-toggle-switch-outline"></i> Status</th>
                            <th><i class="mdi mdi-cog-outline"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody data-infinite-body>
                        @include('team.rows')
                        @if ($members->isEmpty())
                            <tr data-infinite-empty>
                                <td colspan="6" class="text-center">No team members found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @include('partials.infinite-scroll', ['paginator' => $members])
            </div>
        </div>
    </div>
@endsection
