<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Dashboard') }} — {{ config('app.name') }}</title>

        <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
        <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/solid.min.css') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page p-6">
        <main class="auth-shell mx-auto">
            <div class="auth-card p-8">
                <div class="auth-brand mb-6">
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                    <span>{{ config('app.name') }}</span>
                </div>

                <h1 class="auth-title mb-2">{{ __('Dashboard') }}</h1>
                <p class="auth-subtitle mb-8">
                    {{ __('You are signed in as :name.', ['name' => auth()->user()->name]) }}
                </p>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="auth-button">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        <span>{{ __('Log out') }}</span>
                    </button>
                </form>
            </div>
        </main>
    </body>
</html>
