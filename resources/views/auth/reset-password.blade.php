@extends('layouts.guest')

@section('title', 'Reset password')

@section('content')
    <h4 class="mb-3">Choose a new password</h4>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.field name="email" label="Email">
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        </x-ui.field>

        <x-ui.field name="password" label="New Password" icon="mdi-lock-outline">
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
        </x-ui.field>

        <x-ui.field name="password_confirmation" label="Confirm Password" icon="mdi-lock-check">
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
        </x-ui.field>

        <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-lock-reset mr-1"></i> Reset Password</button>
    </form>
@endsection
