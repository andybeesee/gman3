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
                @include('teams.partials.projects-table', ['projects' => $activeProjects])
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

</div>
