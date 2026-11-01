@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <h4 class="mb-1">Welcome back</h4>
    <p class="text-muted mb-3">Log in to manage your pool service business.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <x-ui.field name="email" label="Email">
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="username">
        </x-ui.field>

        <x-ui.field name="password" label="Password" icon="mdi-lock-outline">
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
        </x-ui.field>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="remember_me" name="remember">
                <label class="custom-control-label" for="remember_me">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-login mr-1"></i> Log in</button>
    </form>

    @if (Route::has('register'))
        <div class="auth-foot">
            New pool service business? <a href="{{ route('register') }}" class="font-weight-bold">Register your company</a>
        </div>
    @endif
@endsection
