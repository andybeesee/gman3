<div class="team-dashboard">
    <div class="team-dashboard__main">
        <section class="task-panel">
            <div class="task-panel__header">
                <h2 class="task-panel__title">{{ __('Active projects') }}</h2>
                <a href="{{ route('users.show', ['user' => $user, 'tab' => 'projects']) }}" class="team-panel__link">
                    {{ __('View all') }}
                </a>
            </div>

            @if ($activeProjects->isEmpty())
                <p class="task-empty">{{ __('No active projects for this user.') }}</p>
            @else
                @include('teams.partials.projects-table', ['projects' => $activeProjects, 'showTeams' => true])
            @endif
        </section>

        <section class="task-panel">
            <div class="task-panel__header">
                <h2 class="task-panel__title">{{ __('Active tasks') }}</h2>
                <a href="{{ route('users.show', ['user' => $user, 'tab' => 'tasks']) }}" class="team-panel__link">
                    {{ __('View all') }}
                </a>
            </div>

            @if ($activeTasks->isEmpty())
                <p class="task-empty">{{ __('No active tasks for this user.') }}</p>
            @else
                @include('teams.partials.tasks-table', ['tasks' => $activeTasks, 'statuses' => $statuses])
            @endif
        </section>
    </div>

    <aside class="team-dashboard__sidebar">
        <section class="task-panel">
            <div class="task-panel__header">
                <h2 class="task-panel__title">{{ __('Direct reports') }}</h2>
                <span class="task-panel__count">
                    {{ $subordinates->count() }}
                </span>
            </div>

            @if ($subordinates->isEmpty())
                <p class="task-empty">{{ __('No direct reports.') }}</p>
            @else
                <ul class="team-member-list">
                    @foreach ($subordinates as $subordinate)
                        <li class="team-member-list__item">
                            <a href="{{ route('users.show', $subordinate) }}" class="task-table__title-link">
                                {{ $subordinate->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </aside>
</div>
