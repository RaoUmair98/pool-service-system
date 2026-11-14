<div class="card">
    <div class="card-body">
        <h4 class="header-title danger mb-1"><i class="mdi mdi-account-remove-outline"></i> Delete Account</h4>
        <p class="text-muted mb-2">Your account is removed for good and you can no longer log in. Enter your password to confirm.</p>

        <form method="post" action="{{ route('profile.destroy') }}" data-confirm="Your account will be deleted for ever.">
            @csrf
            @method('delete')

            <div class="form-group">
                <label for="delete_password"><i class="mdi mdi-lock-outline"></i> Password</label>
                <input type="password" id="delete_password" name="password" autocomplete="current-password"
                    class="form-control {{ $errors->userDeletion->has('password') ? 'is-invalid' : '' }}">
                @foreach ($errors->userDeletion->get('password') as $message)
                    <div class="text-danger small mt-1"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-soft-danger"><i class="mdi mdi-delete-outline"></i> Delete Account</button>
        </form>
    </div>
</div>
