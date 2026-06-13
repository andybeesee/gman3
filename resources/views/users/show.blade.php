<x-layouts.app :title="$user->name">
    <div class="dashboard-header">
        <a href="{{ route('users.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Users') }}</span>
        </a>

        <h1 class="dashboard-title">{{ $user->name }}</h1>
        <p class="dashboard-subtitle">{{ $user->email }}</p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <x-user-tabs :user="$user" :tab="$tab" />

    @include('users.tabs.'.$tab)
</x-layouts.app>
