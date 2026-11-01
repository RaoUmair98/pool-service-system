@extends('layouts.guest')

@section('title', 'Verify email')

@section('content')
    <h4 class="mb-1">Verify your email</h4>
    <p class="text-muted mb-3">
        Thanks for signing up! Please click the link we just emailed to you. If it did not arrive, we can send another.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">A new verification link has been sent to your email address.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-email-sync-outline mr-1"></i> Resend Verification Email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="auth-foot">
        @csrf
        <button type="submit" class="btn btn-link font-weight-bold p-0">Log out</button>
    </form>
@endsection
