<div class="card">
    <div class="card-body">
        <h4 class="header-title mb-1"><i class="mdi mdi-account-circle-outline"></i> Profile Information</h4>
        <p class="text-muted mb-2">Your name and the email you log in with.</p>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success"><i class="mdi mdi-check-circle-outline mr-1"></i> Profile saved.</div>
        @endif

        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <x-ui.field name="name" label="Name" required icon="mdi-account-outline">
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
            </x-ui.field>

            <x-ui.field name="email" label="Email" required>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            </x-ui.field>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="text-muted">
                    Your email address is unverified.
                    <button form="send-verification" class="btn btn-link p-0 align-baseline">Re-send the verification email.</button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">A new verification link has been sent to your email address.</div>
                @endif
            @endif

            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
        </form>
    </div>
</div>
