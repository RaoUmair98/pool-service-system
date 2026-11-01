<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background: linear-gradient(135deg, #4f6ef7 0%, #8a5cf6 55%, #f0569b 100%);">
            <div style="text-align: center; color: #fff;">
                <a href="/" style="font-size: 2.2rem; font-weight: 800; letter-spacing: .3px;">{{ config('app.name') }}</a>
                <div style="font-size: 1rem; opacity: .9;">Pool Service Management</div>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white overflow-hidden" style="border-radius: 18px; box-shadow: 0 18px 40px rgba(30, 30, 80, .28);">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
