@props(['title' => ''])

@php
    $user = auth()->user();
    $userInitials = collect(explode(' ', $user->name))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
        ->join('');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name') }}</title>

    <script>
        (function () {
            const theme = localStorage.getItem('theme') ?? 'system';
            const html = document.documentElement;

            html.classList.remove('light', 'dark');

            if (theme === 'light') {
                html.classList.add('light');
            } else if (theme === 'dark') {
                html.classList.add('dark');
            }
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/solid.min.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-page">
    <div class="app-layout" data-app-layout>
        <script>
            (function () {
                const layout = document.currentScript.parentElement;

                if (localStorage.getItem('sidebar-collapsed') === 'true') {
                    layout.classList.add('is-collapsed');

                    const toggle = layout.querySelector('[data-sidebar-toggle]');

                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                }
            })();
        </script>

        <aside class="app-sidebar" aria-label="{{ __('Application sidebar') }}">
            <div class="app-sidebar__header">
                <div class="app-sidebar__brand" data-sidebar-tooltip="{{ config('app.name') }}">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                    <span class="app-sidebar__brand-text">{{ config('app.name') }}</span>
                </div>

                <button
                    type="button"
                    class="app-sidebar__toggle"
                    data-sidebar-toggle
                    aria-expanded="true"
                    aria-label="{{ __('Toggle sidebar') }}"
                    data-sidebar-tooltip="{{ __('Toggle sidebar') }}"
                >
                    <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
                </button>
            </div>

            <nav class="app-sidebar__nav" aria-label="{{ __('Main navigation') }}">
                <a
                    href="#"
                    class="app-sidebar__link"
                    data-sidebar-tooltip="{{ __('Tasks') }}"
                >
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                    <span class="app-sidebar__link-text">{{ __('Tasks') }}</span>
                </a>

                <a
                    href="#"
                    class="app-sidebar__link"
                    data-sidebar-tooltip="{{ __('Projects') }}"
                >
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                    <span class="app-sidebar__link-text">{{ __('Projects') }}</span>
                </a>

                <a
                    href="#"
                    class="app-sidebar__link"
                    data-sidebar-tooltip="{{ __('Teams') }}"
                >
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                    <span class="app-sidebar__link-text">{{ __('Teams') }}</span>
                </a>
            </nav>

            <div class="app-sidebar__footer">
                <div class="app-sidebar__user" data-sidebar-tooltip="{{ $user->name }}">
                    <span class="app-sidebar__user-avatar" aria-hidden="true">{{ $userInitials }}</span>
                    <div class="app-sidebar__user-info">
                        <div class="app-sidebar__user-name">{{ $user->name }}</div>
                        <div class="app-sidebar__user-email">{{ $user->email }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="app-sidebar__action"
                        data-sidebar-tooltip="{{ __('Sign out') }}"
                    >
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        <span class="app-sidebar__action-text">{{ __('Sign out') }}</span>
                    </button>
                </form>

                <div class="app-sidebar__theme">
                    <div class="app-sidebar__theme-menu" data-theme-menu role="menu" aria-label="{{ __('Theme options') }}">
                        <button type="button" class="app-sidebar__theme-option" data-theme-option="light" role="menuitem">
                            <i class="fa-solid fa-sun" aria-hidden="true"></i>
                            <span>{{ __('Light') }}</span>
                        </button>
                        <button type="button" class="app-sidebar__theme-option" data-theme-option="dark" role="menuitem">
                            <i class="fa-solid fa-moon" aria-hidden="true"></i>
                            <span>{{ __('Dark') }}</span>
                        </button>
                        <button type="button" class="app-sidebar__theme-option" data-theme-option="system" role="menuitem">
                            <i class="fa-solid fa-desktop" aria-hidden="true"></i>
                            <span>{{ __('System') }}</span>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="app-sidebar__action"
                        data-theme-toggle
                        aria-haspopup="menu"
                        data-sidebar-tooltip="{{ __('Theme') }}"
                    >
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                        <span class="app-sidebar__action-text">{{ __('Theme') }}</span>
                    </button>
                </div>
            </div>
        </aside>

        <main class="app-main">
            <div class="app-main__inner">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
