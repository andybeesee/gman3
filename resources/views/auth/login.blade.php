@extends('layouts.auth')

@section('title', __('Log in'))

@section('content')
    <div class="auth-shell">
        <div class="auth-card p-8">
            <div class="auth-brand mb-6">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <span>{{ config('app.name') }}</span>
            </div>

            <header class="mb-8">
                <h1 class="auth-title mb-2">{{ __('Welcome back') }}</h1>
                <p class="auth-subtitle">{{ __('Sign in to your account to continue.') }}</p>
            </header>

            @if (session('status'))
                <div class="auth-alert auth-alert--success mb-6" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="auth-alert auth-alert--error mb-6" role="alert">
                    <ul class="auth-errors">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
                @csrf

                <div class="auth-field gap-2">
                    <label for="email" class="auth-label">{{ __('Email address') }}</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="auth-input"
                            value="{{ old('email') }}"
                            placeholder="admin@example.com"
                            required
                            autofocus
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="auth-field gap-2">
                    <label for="password" class="auth-label">{{ __('Password') }}</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="auth-input"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <label for="remember" class="auth-remember">
                    <input id="remember" type="checkbox" name="remember" @checked(old('remember'))>
                    <span>{{ __('Remember me') }}</span>
                </label>

                <button type="submit" class="auth-button">
                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                    <span>{{ __('Log in') }}</span>
                </button>
            </form>
        </div>
    </div>
@endsection
