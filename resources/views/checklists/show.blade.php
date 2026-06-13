<x-layouts.app :title="$checklist->title">
    <div class="dashboard-header">
        <a href="{{ route('checklists.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Checklists') }}</span>
        </a>

        <h1 class="dashboard-title">{{ $checklist->title }}</h1>

        @if ($checklist->description)
            <p class="dashboard-subtitle">{{ $checklist->description }}</p>
        @endif

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="project-meta" aria-label="{{ __('Checklist details') }}">
        <dl class="project-meta__list">
            <div class="project-meta__item">
                <dt>{{ __('Visibility') }}</dt>
                <dd>{{ $checklist->isPublic() ? __('Public') : __('Private') }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Owner') }}</dt>
                <dd>
                    @if ($checklist->owner instanceof \App\Models\User)
                        {{ $checklist->owner->name }}
                    @elseif ($checklist->owner instanceof \App\Models\Team)
                        {{ $checklist->owner->name }}
                    @elseif ($checklist->owner instanceof \App\Models\Project)
                        <a href="{{ route('projects.show', $checklist->owner) }}" class="task-table__title-link">
                            {{ $checklist->owner->title }}
                        </a>
                    @else
                        <span class="task-table__muted">—</span>
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Teams') }}</dt>
                <dd>
                    @if ($checklist->teams->isEmpty())
                        <span class="task-table__muted">—</span>
                    @else
                        {{ $checklist->teams->pluck('name')->join(', ') }}
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Tasks') }}</dt>
                <dd>{{ number_format($tasks->count()) }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Start') }}</dt>
                <dd>{{ $checklist->start_date?->format('M j, Y') ?? '—' }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Due') }}</dt>
                <dd>{{ $checklist->due_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section
        class="task-panel"
        data-checklist-sortable
        data-url="{{ route('checklists.tasks.order.update', $checklist) }}"
    >
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Tasks') }}</h2>
            <span class="task-panel__count" data-checklist-sort-status>
                {{ trans_choice(':count task|:count tasks', $tasks->count(), ['count' => $tasks->count()]) }}
            </span>
        </div>

        <p class="checklist-sortable__error" data-checklist-sort-error hidden></p>

        @if ($tasks->isEmpty())
            <p class="task-empty">{{ __('No tasks have been added to this checklist yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table checklist-task-table">
                    <thead>
                        <tr>
                            <th scope="col" class="checklist-task-table__sort-heading">
                                <span class="sr-only">{{ __('Sort') }}</span>
                            </th>
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
                    <tbody data-checklist-sort-list>
                        @foreach ($tasks as $task)
                            @php
                                $status = $task->status;
                            @endphp
                            <tr data-checklist-sort-row data-task-id="{{ $task->id }}">
                                <td class="checklist-task-table__sort">
                                    @can('reorderTasks', $checklist)
                                        <button type="button" class="checklist-sort-handle" data-checklist-sort-handle aria-label="{{ __('Drag to reorder :task', ['task' => $task->title]) }}">
                                            <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="task-table__muted">—</span>
                                    @endcan
                                </td>
                                <td class="task-table__title">
                                    <span class="task-table__title-content">
                                        <span class="task-table__title-text" title="{{ $task->title }}">
                                            {{ $task->title }}
                                        </span>
                                        <x-visibility-indicator :resource="$task" />
                                    </span>
                                </td>
                                <td>
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
</x-layouts.app>
