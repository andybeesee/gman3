<x-layouts.app title="Dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Dashboard') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <livewire:task-list context="dashboard" />
</x-layouts.app>
