<div class="team-dashboard">
    <div class="team-dashboard__main">
        <section class="task-panel">
            <div class="task-panel__header">
                <h2 class="task-panel__title">{{ __('Active projects') }}</h2>
                <a href="{{ route('teams.show', ['team' => $team, 'tab' => 'projects']) }}" class="team-panel__link">
                    {{ __('View all') }}
                </a>
            </div>

            @if ($activeProjects->isEmpty())
                <p class="task-empty">{{ __('No active projects for this team.') }}</p>
            @else
                @include('teams.partials.projects-table', ['projects' => $activeProjects])
            @endif
        </section>

        <section class="task-panel">
            <div class="task-panel__header">
                <h2 class="task-panel__title">{{ __('Active tasks') }}</h2>
                <a href="{{ route('teams.show', ['team' => $team, 'tab' => 'tasks']) }}" class="team-panel__link">
                    {{ __('View all') }}
                </a>
            </div>

            @if ($activeTasks->isEmpty())
                <p class="task-empty">{{ __('No active tasks for this team.') }}</p>
            @else
                @include('teams.partials.tasks-table', ['tasks' => $activeTasks, 'statuses' => $statuses])
            @endif
        </section>
    </div>

    <aside class="team-dashboard__sidebar">
        <section class="task-panel">
            <div class="task-panel__header">
                <h2 class="task-panel__title">{{ __('Members') }}</h2>
                <span class="task-panel__count">
                    {{ $members->count() }}
                </span>
            </div>

            @if ($members->isEmpty())
                <p class="task-empty">{{ __('No members yet.') }}</p>
            @else
                <ul class="team-member-list">
                    @foreach ($members as $member)
                        <li class="team-member-list__item">{{ $member->name }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    </aside>
</div>
