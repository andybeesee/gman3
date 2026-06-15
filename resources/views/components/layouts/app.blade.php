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

            html.classList.remove('light', 'dark', 'neon');

            if (theme === 'light') {
                html.classList.add('light');
            } else if (theme === 'dark') {
                html.classList.add('dark');
            } else if (theme === 'neon') {
                html.classList.add('neon');
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

                    layout.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => {
                        toggle.setAttribute('aria-expanded', 'false');
                    });
                }
            })();
        </script>

        <aside class="app-sidebar" aria-label="{{ __('Application sidebar') }}">
            <div class="app-sidebar__header">
                <a
                    href="{{ route('dashboard') }}"
                    class="app-sidebar__menu-button app-sidebar__menu-button--lg app-sidebar__brand"
                    data-sidebar-tooltip="{{ config('app.name') }}"
                >
                    <span class="app-sidebar__brand-icon" aria-hidden="true">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                    <span class="app-sidebar__menu-button-text">{{ config('app.name') }}</span>
                </a>

                <button
                    type="button"
                    class="app-sidebar__collapse"
                    data-sidebar-toggle
                    aria-expanded="true"
                    aria-label="{{ __('Toggle sidebar') }}"
                    data-sidebar-tooltip="{{ __('Toggle sidebar') }}"
                >
                    <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
                </button>
            </div>

            <div class="app-sidebar__content">
                <div class="app-sidebar__group">
                    <div class="app-sidebar__group-label">{{ __('Workspace') }}</div>
                    <ul class="app-sidebar__menu">
                        <li class="app-sidebar__menu-item">
                            <a
                                href="{{ route('tasks.create') }}"
                                class="app-sidebar__menu-button app-sidebar__menu-button--primary"
                                data-sidebar-tooltip="{{ __('New task') }}"
                            >
                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                <span class="app-sidebar__menu-button-text">{{ __('New task') }}</span>
                            </a>
                        </li>
                        <li class="app-sidebar__menu-item">
                            <a
                                href="{{ route('projects.index') }}"
                                class="app-sidebar__menu-button"
                                data-sidebar-tooltip="{{ __('Projects') }}"
                            >
                                <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                                <span class="app-sidebar__menu-button-text">{{ __('Projects') }}</span>
                            </a>
                        </li>
                        <li class="app-sidebar__menu-item">
                            <a
                                href="{{ route('tasks.index') }}"
                                class="app-sidebar__menu-button"
                                data-sidebar-tooltip="{{ __('Tasks') }}"
                            >
                                <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                                <span class="app-sidebar__menu-button-text">{{ __('Tasks') }}</span>
                            </a>
                        </li>
                        <li class="app-sidebar__menu-item">
                            <a
                                href="{{ route('checklists.index') }}"
                                class="app-sidebar__menu-button"
                                data-sidebar-tooltip="{{ __('Checklists') }}"
                            >
                                <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                <span class="app-sidebar__menu-button-text">{{ __('Checklists') }}</span>
                            </a>
                        </li>
                        <li class="app-sidebar__menu-item">
                            <a
                                href="{{ route('teams.index') }}"
                                class="app-sidebar__menu-button"
                                data-sidebar-tooltip="{{ __('Teams') }}"
                            >
                                <i class="fa-solid fa-user-group" aria-hidden="true"></i>
                                <span class="app-sidebar__menu-button-text">{{ __('Teams') }}</span>
                            </a>
                        </li>
                        <li class="app-sidebar__menu-item">
                            <a
                                href="{{ route('users.index') }}"
                                class="app-sidebar__menu-button"
                                data-sidebar-tooltip="{{ __('Users') }}"
                            >
                                <i class="fa-solid fa-address-book" aria-hidden="true"></i>
                                <span class="app-sidebar__menu-button-text">{{ __('Users') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                @if ($user->isSuperAdmin())
                    <div class="app-sidebar__group">
                        <div class="app-sidebar__group-label">{{ __('Administration') }}</div>
                        <ul class="app-sidebar__menu">
                            <li class="app-sidebar__menu-item">
                                <a
                                    href="{{ route('statuses.index') }}"
                                    class="app-sidebar__menu-button"
                                    data-sidebar-tooltip="{{ __('Statuses') }}"
                                >
                                    <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
                                    <span class="app-sidebar__menu-button-text">{{ __('Statuses') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>

            <div class="app-sidebar__footer">
                <div class="app-sidebar__group">
                    <ul class="app-sidebar__menu">
                        <li class="app-sidebar__menu-item">
                            <div class="app-sidebar__theme">
                                <div
                                    class="app-sidebar__dropdown"
                                    data-theme-menu
                                    role="menu"
                                    aria-label="{{ __('Theme options') }}"
                                >
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
                                    <button type="button" class="app-sidebar__theme-option" data-theme-option="neon" role="menuitem">
                                        <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                                        <span>{{ __('Neon') }}</span>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    class="app-sidebar__menu-button"
                                    data-theme-toggle
                                    aria-haspopup="menu"
                                    data-sidebar-tooltip="{{ __('Theme') }}"
                                >
                                    <i class="fa-solid fa-palette" aria-hidden="true"></i>
                                    <span class="app-sidebar__menu-button-text">{{ __('Theme') }}</span>
                                </button>
                            </div>
                        </li>

                        <li class="app-sidebar__menu-item">
                            <div class="app-sidebar__user-menu">
                                <div
                                    class="app-sidebar__dropdown"
                                    data-user-menu
                                    role="menu"
                                    aria-label="{{ __('Account menu') }}"
                                >
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="app-sidebar__dropdown-item" role="menuitem">
                                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                                            <span>{{ __('Sign out') }}</span>
                                        </button>
                                    </form>
                                </div>

                                <button
                                    type="button"
                                    class="app-sidebar__menu-button app-sidebar__menu-button--lg"
                                    data-user-toggle
                                    aria-haspopup="menu"
                                    aria-expanded="false"
                                    data-sidebar-tooltip="{{ $user->name }}"
                                >
                                    <span class="app-sidebar__avatar" aria-hidden="true">{{ $userInitials }}</span>
                                    <span class="app-sidebar__user-copy">
                                        <span class="app-sidebar__user-name">{{ $user->name }}</span>
                                        <span class="app-sidebar__user-email">{{ $user->email }}</span>
                                    </span>
                                    <span class="app-sidebar__menu-button-meta" aria-hidden="true">
                                        <i class="fa-solid fa-angles-up-down"></i>
                                    </span>
                                </button>
                            </div>
                        </li>
                    </ul>
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
