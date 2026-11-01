{{-- Search + status bar for the master lists. Optional: $placeholder, $roles (value => label). --}}
<form method="GET" class="form-row align-items-end mb-3">
    <div class="col-md-3 mb-2">
        <label class="mb-1"><i class="mdi mdi-magnify"></i> Search</label>
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ $placeholder ?? 'Search by name' }}">
    </div>

    @isset($roles)
        <div class="col-md-2 mb-2">
            <label class="mb-1"><i class="mdi mdi-shield-account-outline"></i> Role</label>
            <select name="role" class="form-control">
                <option value="">All roles</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endisset

    <div class="col-md-2 mb-2">
        <label class="mb-1"><i class="mdi mdi-toggle-switch-outline"></i> Status</label>
        <select name="status" class="form-control">
            <option value="">All</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
    </div>

    <div class="col-md-auto mb-2">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
    </div>
</form>
