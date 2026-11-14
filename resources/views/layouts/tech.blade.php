<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', "Today's Route") | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0369a1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('assets/css/bootstrap-corporate.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.css') }}?v={{ filemtime(public_path('assets/css/custom.css')) }}" rel="stylesheet" type="text/css" />
    @stack('styles')
</head>
<body class="tech-app">
    <header class="tech-topbar">
        <div class="tech-brand">
            <i class="mdi mdi-pool"></i>
            <span>{{ auth()->user()->company?->name ?? config('app.name') }}</span>
        </div>
        <div class="tech-user dropdown">
            <a href="#" class="tech-user-btn" data-toggle="dropdown">
                {{ auth()->user()->name }} <i class="mdi mdi-chevron-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="mdi mdi-account-circle-outline"></i> My Account</a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item"><i class="mdi mdi-logout"></i> Logout</button>
                </form>
            </div>
        </div>
    </header>

    <nav class="tech-nav">
        <a href="{{ route('route.index') }}" class="{{ request()->routeIs('route.*') ? 'active' : '' }}">
            <i class="mdi mdi-map-marker-path"></i> Today's Route
        </a>
    </nav>

    <main class="tech-main">
        @include('partials.toasts')

        @yield('content')
    </main>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.querySelectorAll('.toast-close').forEach(function (btn) {
            btn.addEventListener('click', function () { btn.closest('.app-toast')?.remove(); });
        });
        document.querySelectorAll('.app-toast[data-autohide]').forEach(function (toast) {
            setTimeout(function () { toast.remove(); }, parseInt(toast.dataset.autohide, 10) || 6000);
        });
    </script>
    @stack('scripts')
</body>
</html>
