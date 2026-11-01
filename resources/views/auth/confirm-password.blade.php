@extends('layouts.guest')

@section('title', 'Confirm password')

@section('content')
    <h4 class="mb-1">Confirm your password</h4>
    <p class="text-muted mb-3">This is a secure area. Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <x-ui.field name="password" label="Password" icon="mdi-lock-outline">
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autofocus autocomplete="current-password">
        </x-ui.field>

        <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-shield-check-outline mr-1"></i> Confirm</button>
    </form>
@endsection
