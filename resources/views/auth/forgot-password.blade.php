@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <h4 class="mb-1">Forgot your password?</h4>
    <p class="text-muted mb-3">Enter your email and we will send you a link to choose a new one.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <x-ui.field name="email" label="Email">
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
        </x-ui.field>

        <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-email-send-outline mr-1"></i> Email Password Reset Link</button>
    </form>

    <div class="auth-foot">
        <a href="{{ route('login') }}" class="font-weight-bold"><i class="mdi mdi-arrow-left"></i> Back to log in</a>
    </div>
@endsection
