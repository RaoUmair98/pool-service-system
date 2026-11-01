@extends('layouts.admin')

@section('title', 'Team')

@section('help')
    Everyone who works for {{ auth()->user()->company->name }}. <strong>Admins</strong> (owner / office) manage the company and the team;
    <strong>Technicians</strong> do the pool visits. Turn someone off to stop them logging in without losing their history.
@endsection

@section('actions')
    <a href="{{ route('team.create', ['role' => \App\Models\User::ROLE_TECHNICIAN]) }}" class="btn btn-primary"><i class="mdi mdi-account-hard-hat"></i> Add Technician</a>
    <a href="{{ route('team.create', ['role' => \App\Models\User::ROLE_ADMIN]) }}" class="btn btn-light ml-1"><i class="mdi mdi-shield-account-outline"></i> Add Admin</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @include('partials.master-filters', [
                'placeholder' => 'Search by name, email or phone',
                'roles' => array_map(fn ($role) => $role[0], \App\Models\User::ROLES),
            ])

            <div class="table-responsive">
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
                    <tbody>
                        @forelse ($members as $member)
                            @php [$roleLabel, $roleBadge, $roleIcon] = \App\Models\User::ROLES[$member->role_name] ?? ['-', 'secondary', 'mdi-account-outline']; @endphp
                            <tr>
                                <td class="name-cell">
                                    {{ $member->name }}
                                    @if ($member->is(auth()->user()))
                                        <span class="badge badge-light ml-1">You</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $roleBadge }}"><i class="mdi {{ $roleIcon }}"></i> {{ $roleLabel }}</span></td>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->phone ?: '-' }}</td>
                                <td><span class="badge badge-{{ $member->is_active ? 'success' : 'secondary' }}">{{ $member->is_active ? 'Active' : 'Turned off' }}</span></td>
                                <td>
                                    @if ($member->is(auth()->user()))
                                        <a href="{{ route('team.edit', $member) }}" class="btn btn-xs btn-light"><i class="mdi mdi-pencil"></i> Edit</a>
                                    @else
                                        @include('partials.row-actions', ['name' => 'team', 'model' => $member])
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No team members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.pagination', ['paginator' => $members])
        </div>
    </div>
@endsection
