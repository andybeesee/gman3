<x-layouts.app title="Dashboard">
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
</x-layouts.app>
