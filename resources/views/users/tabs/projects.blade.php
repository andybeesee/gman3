<section class="task-panel">
    <div class="task-panel__header">
        <h2 class="task-panel__title">{{ __('All projects') }}</h2>
        <span class="task-panel__count">
            {{ trans_choice(':count project|:count projects', $projects->total(), ['count' => $projects->total()]) }}
        </span>
    </div>

    @if ($projects->isEmpty())
        <p class="task-empty">{{ __('No projects are owned by this user yet.') }}</p>
    @else
        @include('teams.partials.projects-table', ['projects' => $projects, 'showTeams' => true])
    @endif
</section>

@if (! $projects->isEmpty())
    <x-task-pagination :paginator="$projects" />
@endif
