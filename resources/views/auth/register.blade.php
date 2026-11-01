@extends('layouts.guest')

@section('title', 'Register your company')

@section('card-class', 'auth-card-wide')

@section('content')
    <h4 class="mb-1">Register your company</h4>
    <p class="text-muted mb-3">You will be the admin. Add your technicians after signing in.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-row">
            <div class="col-sm-7">
                <x-ui.field name="company_name" label="Company Name" required icon="mdi-domain">
                    <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required autofocus autocomplete="organization">
                </x-ui.field>
            </div>
            <div class="col-sm-5">
                <x-ui.field name="company_phone" label="Company Phone" icon="mdi-phone-outline">
                    <input type="tel" id="company_phone" name="company_phone" class="form-control @error('company_phone') is-invalid @enderror" value="{{ old('company_phone') }}" autocomplete="tel">
                </x-ui.field>
            </div>
        </div>

        <x-ui.field name="name" label="Your Name" required icon="mdi-account-outline">
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name">
        </x-ui.field>

        <x-ui.field name="email" label="Email" required hint="You will log in with this email.">
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username">
        </x-ui.field>

        <div class="form-row">
            <div class="col-sm-6">
                <x-ui.field name="password" label="Password" required icon="mdi-lock-outline">
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                </x-ui.field>
            </div>
            <div class="col-sm-6">
                <x-ui.field name="password_confirmation" label="Confirm Password" required icon="mdi-lock-check">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </x-ui.field>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-pool mr-1"></i> Create Account</button>
    </form>

    <div class="auth-foot">
        Already registered? <a href="{{ route('login') }}" class="font-weight-bold">Log in</a>
    </div>
@endsection
