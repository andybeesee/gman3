<x-layouts.app title="Dashboard">
    <div class="auth-card p-8">
        <h1 class="auth-title mb-2">{{ __('Dashboard') }}</h1>
        <p class="auth-subtitle">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </p>
    </div>
</x-layouts.app>
