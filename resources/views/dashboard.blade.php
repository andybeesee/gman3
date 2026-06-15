<x-layouts.app title="Dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Dashboard') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    {{-- Projects --}}
    <section class="dash-section" aria-label="{{ __('Projects') }}">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Projects') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count project|:count projects', $projects->count(), ['count' => $projects->count()]) }}
            </span>
        </div>

        @if ($projects->isEmpty())
            <p class="task-empty">{{ __('No projects are visible to you yet.') }}</p>
        @else
            <div class="project-cards">
                @foreach ($projects as $project)
                    @php $projectStatus = $project->status; @endphp
                    <div class="project-card" data-project-card>
                        <div class="project-card__header">
                            <button
                                type="button"
                                class="project-card__toggle"
                                aria-expanded="false"
                                data-project-toggle
                                aria-controls="project-body-{{ $project->id }}"
                            >
                                <i class="fa-solid fa-chevron-right project-card__caret" aria-hidden="true"></i>
                                <span class="project-card__title">{{ $project->title }}</span>
                            </button>
                            <span class="project-card__meta">
                                @if ($projectStatus)
                                    <span
                                        class="task-status"
                                        style="--status-light: {{ $projectStatus->light_theme_color }}; --status-dark: {{ $projectStatus->dark_theme_color }};"
                                    >
                                        <i class="fa-solid {{ $projectStatus->fontAwesomeIcon() }}" aria-hidden="true"></i>
                                        <span>{{ $projectStatus->name }}</span>
                                    </span>
                                @endif
                                <span class="project-card__task-count">
                                    {{ trans_choice(':count open task|:count open tasks', $project->ownedTasks->count(), ['count' => $project->ownedTasks->count()]) }}
                                </span>
                                @if ($project->due_date)
                                    <span class="project-card__due">
                                        <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
                                        {{ $project->due_date->format('M j, Y') }}
                                    </span>
                                @endif
                            </span>
                            <a
                                href="{{ route('projects.show', $project) }}"
                                class="project-card__link"
                                title="{{ __('Open project') }}"
                            >
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                <span class="sr-only">{{ __('Open project') }}</span>
                            </a>
                        </div>

                        <div class="project-card__body" id="project-body-{{ $project->id }}" hidden>
                            @if ($project->ownedTasks->isNotEmpty())
                                <div class="task-table-wrap">
                                    <table class="task-table">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Task') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Due date') }}</th>
                                                <th scope="col" class="task-table__actions-heading">
                                                    <span class="sr-only">{{ __('Actions') }}</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($project->ownedTasks as $task)
                                                @php $taskStatus = $task->status; @endphp
                                                <tr>
                                                    <td class="task-table__title">
                                                        <span class="task-table__title-text" title="{{ $task->title }}">
                                                            {{ $task->title }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($taskStatus)
                                                            <span
                                                                class="task-status"
                                                                style="--status-light: {{ $taskStatus->light_theme_color }}; --status-dark: {{ $taskStatus->dark_theme_color }};"
                                                            >
                                                                <i class="fa-solid {{ $taskStatus->fontAwesomeIcon() }}" aria-hidden="true"></i>
                                                                <span>{{ $taskStatus->name }}</span>
                                                            </span>
                                                        @else
                                                            <span class="task-table__muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <form
                                                            method="POST"
                                                            action="{{ route('tasks.due-date.update', $task) }}"
                                                            class="inline-date-form"
                                                        >
                                                            @csrf
                                                            @method('PATCH')
                                                            <input
                                                                type="date"
                                                                name="due_date"
                                                                class="inline-date-input"
                                                                value="{{ $task->due_date?->format('Y-m-d') }}"
                                                                aria-label="{{ __('Due date for :task', ['task' => $task->title]) }}"
                                                                onchange="this.form.submit()"
                                                            >
                                                        </form>
                                                    </td>
                                                    <td class="task-table__actions">
                                                        <x-task-actions :task="$task" :statuses="$statuses" />
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('projects.tasks.store', $project) }}"
                                class="project-card__add-task"
                            >
                                @csrf
                                <i class="fa-solid fa-plus project-card__add-icon" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    name="title"
                                    class="project-card__add-input"
                                    placeholder="{{ __('Add a task…') }}"
                                    required
                                    autocomplete="off"
                                >
                                <input
                                    type="date"
                                    name="due_date"
                                    class="project-card__add-date"
                                    aria-label="{{ __('Due date') }}"
                                >
                                <button type="submit" class="project-card__add-btn">
                                    {{ __('Add') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Tasks not belonging to any project --}}
    <section class="task-panel" style="margin-top: 1.5rem;" aria-label="{{ __('Other tasks') }}">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Other tasks') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count open task|:count open tasks', $standaloneTasks->count(), ['count' => $standaloneTasks->count()]) }}
            </span>
        </div>

        @if ($standaloneTasks->isEmpty())
            <p class="task-empty">{{ __('No other open tasks assigned to you.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Title') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Teams') }}</th>
                            <th scope="col">{{ __('Start') }}</th>
                            <th scope="col">{{ __('Due') }}</th>
                            <th scope="col" class="task-table__actions-heading">
                                <span class="sr-only">{{ __('Actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($standaloneTasks as $task)
                            @php $taskStatus = $task->status; @endphp
                            <tr>
                                <td class="task-table__title">
                                    <span class="task-table__title-content">
                                        <span class="task-table__title-text" title="{{ $task->title }}">
                                            {{ $task->title }}
                                        </span>
                                        <x-visibility-indicator :resource="$task" />
                                    </span>
                                </td>
                                <td>
                                    @if ($taskStatus)
                                        <span
                                            class="task-status"
                                            style="--status-light: {{ $taskStatus->light_theme_color }}; --status-dark: {{ $taskStatus->dark_theme_color }};"
                                        >
                                            <i class="fa-solid {{ $taskStatus->fontAwesomeIcon() }}" aria-hidden="true"></i>
                                            <span>{{ $taskStatus->name }}</span>
                                        </span>
                                    @else
                                        <span class="task-table__muted">—</span>
                                    @endif
                                </td>
                                <td class="task-table__teams">
                                    @if ($task->teams->isEmpty())
                                        <span class="task-table__muted">—</span>
                                    @else
                                        {{ $task->teams->pluck('name')->join(', ') }}
                                    @endif
                                </td>
                                <td class="task-table__date">
                                    {{ $task->start_date?->format('M j, Y') ?? '—' }}
                                </td>
                                <td class="task-table__date">
                                    {{ $task->due_date?->format('M j, Y') ?? '—' }}
                                </td>
                                <td class="task-table__actions">
                                    <x-task-actions :task="$task" :statuses="$statuses" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
