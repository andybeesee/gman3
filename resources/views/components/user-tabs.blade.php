@props(['user', 'tab', 'showHierarchyTab' => false])

@php
    $tabs = [
        'dashboard' => __('Dashboard'),
        'projects' => __('Projects'),
        'tasks' => __('Tasks'),
    ];

    if ($showHierarchyTab) {
        $tabs['hierarchy'] = __('Hierarchy');
    }
@endphp

<nav class="team-tabs" aria-label="{{ __('User sections') }}">
    <ul class="team-tabs__list" role="tablist">
        @foreach ($tabs as $key => $label)
            <li class="team-tabs__item" role="presentation">
                <a
                    href="{{ route('users.show', ['user' => $user, 'tab' => $key]) }}"
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
