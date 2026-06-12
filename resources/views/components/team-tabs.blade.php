@props(['team', 'tab'])

@php
    $tabs = [
        'dashboard' => __('Dashboard'),
        'members' => __('Members'),
        'projects' => __('Projects'),
        'tasks' => __('Tasks'),
    ];
@endphp

<nav class="team-tabs" aria-label="{{ __('Team sections') }}">
    <ul class="team-tabs__list" role="tablist">
        @foreach ($tabs as $key => $label)
            <li class="team-tabs__item" role="presentation">
                <a
                    href="{{ route('teams.show', ['team' => $team, 'tab' => $key]) }}"
                    class="team-tabs__link @if ($tab === $key) is-active @endif"
                    role="tab"
                    @if ($tab === $key) aria-current="page" @endif
                >
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
