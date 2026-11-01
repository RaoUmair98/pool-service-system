<div class="card">
    <div class="card-body">
        <h4 class="header-title mb-1"><i class="mdi mdi-lock-outline"></i> Change Password</h4>
        <p class="text-muted mb-3">Use a long password that is hard to guess.</p>

        @if (session('status') === 'password-updated')
            <div class="alert alert-success"><i class="mdi mdi-check-circle-outline mr-1"></i> Password changed.</div>
        @endif

        <form method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            @foreach ([
                ['current_password', 'Current Password', 'current-password', 'mdi-lock-outline'],
                ['password', 'New Password', 'new-password', 'mdi-lock-plus'],
                ['password_confirmation', 'Confirm New Password', 'new-password', 'mdi-lock-check'],
            ] as [$field, $label, $autocomplete, $icon])
                <div class="form-group">
                    <label for="update_password_{{ $field }}"><i class="mdi {{ $icon }}"></i> {{ $label }}</label>
                    <input type="password" id="update_password_{{ $field }}" name="{{ $field }}" autocomplete="{{ $autocomplete }}"
                        class="form-control {{ $errors->updatePassword->has($field) ? 'is-invalid' : '' }}">
                    @foreach ($errors->updatePassword->get($field) as $message)
                        <div class="text-danger small mt-1"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</div>
                    @endforeach
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
        </form>
    </div>
</div>
