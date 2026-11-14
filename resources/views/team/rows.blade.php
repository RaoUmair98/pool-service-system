@foreach ($members as $member)
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
@endforeach
