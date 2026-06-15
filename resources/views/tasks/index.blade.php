<x-layouts.app title="{{ __('Tasks') }}">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Tasks') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('All tasks across the organization.') }}
        </p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif

        @can('create', \App\Models\Task::class)
            <div class="dashboard-header__actions">
                <a href="{{ route('tasks.create') }}" class="task-form__submit task-form__submit--sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    {{ __('New Task') }}
                </a>
            </div>
        @endcan
    </div>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('All tasks') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count task|:count tasks', $tasks->total(), ['count' => $tasks->total()]) }}
            </span>
        </div>

        @if ($tasks->isEmpty())
            <p class="task-empty">{{ __('No tasks have been created yet.') }}</p>
        @else
            @include('teams.partials.tasks-table', ['tasks' => $tasks, 'statuses' => $statuses])
        @endif
    </section>

    @if (! $tasks->isEmpty())
        <x-task-pagination :paginator="$tasks" />
    @endif
</x-layouts.app>
