<x-layouts.app :title="$team->name">
    <div class="dashboard-header">
        <a href="{{ route('teams.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Teams') }}</span>
        </a>

        <h1 class="dashboard-title">{{ $team->name }}</h1>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <x-team-tabs :team="$team" :tab="$tab" />

    @include('teams.tabs.'.$tab)
</x-layouts.app>
