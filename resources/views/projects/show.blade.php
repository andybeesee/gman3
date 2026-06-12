@php
    $status = $project->status;
@endphp

<x-layouts.app :title="$project->title">
    <div class="dashboard-header">
        <a href="{{ route('projects.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Projects') }}</span>
        </a>

        <h1 class="dashboard-title">{{ $project->title }}</h1>

        @if ($project->description)
            <p class="dashboard-subtitle">{{ $project->description }}</p>
        @endif

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="project-meta" aria-label="{{ __('Project details') }}">
        <dl class="project-meta__list">
            <div class="project-meta__item">
                <dt>{{ __('Status') }}</dt>
                <dd>
                    @if ($status)
                        <span
                            class="task-status"
                            style="--status-light: {{ $status->light_theme_color }}; --status-dark: {{ $status->dark_theme_color }};"
                        >
                            <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
                            <span>{{ $status->name }}</span>
                        </span>
                    @else
                        <span class="task-table__muted">—</span>
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Ownership') }}</dt>
                <dd>
                    @if ($project->isPersonallyOwned())
                        {{ $project->ownerUser?->name ?? __('Personal') }}
                    @else
                        {{ __('Team') }}
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Teams') }}</dt>
                <dd>
                    @if ($project->teams->isEmpty())
                        <span class="task-table__muted">—</span>
                    @else
                        {{ $project->teams->pluck('name')->join(', ') }}
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Start') }}</dt>
                <dd>{{ $project->start_date?->format('M j, Y') ?? '—' }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Due') }}</dt>
                <dd>{{ $project->due_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Tasks') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count task|:count tasks', $tasks->total(), ['count' => $tasks->total()]) }}
            </span>
        </div>

        @if ($tasks->isEmpty())
            <p class="task-empty">{{ __('No tasks have been added to this project yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Title') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Assignees') }}</th>
                            <th scope="col">{{ __('Teams') }}</th>
                            <th scope="col">{{ __('Start') }}</th>
                            <th scope="col">{{ __('Due') }}</th>
                            <th scope="col" class="task-table__actions-heading">
                                <span class="sr-only">{{ __('Actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            @php
                                $taskStatus = $task->status;
                            @endphp
                            <tr>
                                <td class="task-table__title" title="{{ $task->title }}">
                                    {{ $task->title }}
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
                                <td class="task-table__assignees">
                                    @if ($task->assignees->isEmpty())
                                        <span class="task-table__muted">—</span>
                                    @else
                                        {{ $task->assignees->pluck('name')->join(', ') }}
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

    @if (! $tasks->isEmpty())
        <x-task-pagination :paginator="$tasks" />
    @endif
</x-layouts.app>
