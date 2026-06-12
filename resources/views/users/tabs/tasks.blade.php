<section class="task-panel">
    <div class="task-panel__header">
        <h2 class="task-panel__title">{{ __('All tasks') }}</h2>
        <span class="task-panel__count">
            {{ trans_choice(':count task|:count tasks', $tasks->total(), ['count' => $tasks->total()]) }}
        </span>
    </div>

    @if ($tasks->isEmpty())
        <p class="task-empty">{{ __('No tasks are associated with this user yet.') }}</p>
    @else
        @include('teams.partials.tasks-table', ['tasks' => $tasks, 'statuses' => $statuses])
    @endif
</section>

@if (! $tasks->isEmpty())
    <x-task-pagination :paginator="$tasks" />
@endif
