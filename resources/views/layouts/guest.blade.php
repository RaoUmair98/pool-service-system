<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Welcome') | {{ config('app.name') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <meta name="theme-color" content="#0369a1">

        <link href="{{ asset('assets/css/bootstrap-corporate.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app-corporate.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/custom.css') }}?v={{ filemtime(public_path('assets/css/custom.css')) }}" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div class="auth-page">
            <a href="{{ url('/') }}" class="pool-brand">
                <span class="brand-icon"><i class="mdi mdi-pool"></i></span>
                <span class="name">{{ config('app.name') }}</span>
                <span class="tagline">Pool cleaning, maintenance and service management</span>
            </a>

            <div class="auth-card @yield('card-class')">
                @if (session('status') && session('status') !== 'verification-link-sent')
                    <div class="alert alert-success"><i class="mdi mdi-check-circle-outline mr-1"></i> {{ session('status') }}</div>
                @endif

                @yield('content')
            </div>
        </div>
    </body>
</html>
