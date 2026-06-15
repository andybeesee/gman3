<section class="task-panel task-list">
    <div class="task-panel__header">
        <h2 class="task-panel__title">
            {{ $context === 'dashboard' ? __('My tasks') : __('Tasks') }}
        </h2>
        <span class="task-panel__count">
            {{ trans_choice(':count task|:count tasks', $tasks->count(), ['count' => $tasks->count()]) }}
        </span>
    </div>

    @if ($tasks->isEmpty())
        <p class="task-empty" wire:key="task-empty">
            {{ $context === 'dashboard' ? __('No open tasks are assigned to you.') : __('No tasks have been added yet.') }}
        </p>
    @else
        <div class="task-list__groups" wire:key="task-groups">
            @foreach ($taskGroups as $groupKey => $groupTasks)
                @php
                    $firstTask = $groupTasks->first();
                    $owner = $firstTask?->owner;
                    $groupLabel = match (true) {
                        $owner instanceof \App\Models\Project => $owner->title,
                        $owner instanceof \App\Models\Team => $owner->name,
                        $owner instanceof \App\Models\User => __('Personal'),
                        default => __('Other tasks'),
                    };
                @endphp

                <div class="task-list__group" wire:key="task-group-{{ $groupKey }}">
                    @if ($context === 'dashboard')
                        <div class="task-list__group-header">
                            <div class="task-list__group-title">
                                @if ($owner instanceof \App\Models\Project)
                                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                                    <a href="{{ route('projects.show', $owner) }}" class="task-table__title-link">
                                        {{ $groupLabel }}
                                    </a>
                                @elseif ($owner instanceof \App\Models\Team)
                                    <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                                    <span>{{ $groupLabel }}</span>
                                @else
                                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                                    <span>{{ $groupLabel }}</span>
                                @endif
                            </div>
                            <span class="task-list__group-count">
                                {{ trans_choice(':count task|:count tasks', $groupTasks->count(), ['count' => $groupTasks->count()]) }}
                            </span>
                        </div>
                    @endif

                    <div class="task-table-wrap">
                        <table class="task-table">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Task') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Assignees') }}</th>
                                    <th scope="col">{{ __('Teams') }}</th>
                                    <th scope="col">{{ __('Due') }}</th>
                                    <th scope="col" class="task-table__actions-heading">
                                        <span class="sr-only">{{ __('Actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupTasks as $task)
                                    @php $status = $task->status; @endphp
                                    <tr wire:key="task-{{ $task->id }}">
                                        <td class="task-table__title">
                                            <span class="task-table__title-content">
                                                <a
                                                    href="{{ route('tasks.show', $task) }}"
                                                    class="task-table__title-link task-table__title-text"
                                                    title="{{ $task->title }}"
                                                >{{ $task->title }}</a>
                                                <x-visibility-indicator :resource="$task" />
                                            </span>
                                        </td>
                                        <td>
                                            @can('updateStatus', $task)
                                                <select
                                                    class="task-list__status-select status-color-{{ $status?->color ?? 'gray' }}"
                                                    aria-label="{{ __('Status for :task', ['task' => $task->title]) }}"
                                                    wire:change="updateStatus({{ $task->id }}, $event.target.value)"
                                                >
                                                    @foreach ($statuses as $availableStatus)
                                                        <option
                                                            value="{{ $availableStatus->id }}"
                                                            @selected($status?->id === $availableStatus->id)
                                                        >
                                                            {{ $availableStatus->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                @if ($status)
                                                    <span class="task-status status-color-{{ $status->color }}">
                                                        <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
                                                        <span>{{ $status->name }}</span>
                                                    </span>
                                                @else
                                                    <span class="task-table__muted">—</span>
                                                @endif
                                            @endcan
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
                                        <td>
                                            <input
                                                type="date"
                                                class="inline-date-input"
                                                value="{{ $task->due_date?->format('Y-m-d') }}"
                                                aria-label="{{ __('Due date for :task', ['task' => $task->title]) }}"
                                                wire:change="updateDueDate({{ $task->id }}, $event.target.value)"
                                            >
                                        </td>
                                        <td class="task-table__actions">
                                            <a
                                                href="{{ route('tasks.show', $task) }}"
                                                class="project-card__link"
                                                title="{{ __('Open task') }}"
                                            >
                                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                                <span class="sr-only">{{ __('Open task') }}</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <form wire:submit.prevent="addTask" class="project-card__add-task">
        <i class="fa-solid fa-plus project-card__add-icon" aria-hidden="true"></i>
        <input
            wire:model="title"
            type="text"
            class="project-card__add-input @error('title') is-invalid @enderror"
            placeholder="{{ $context === 'dashboard' ? __('Add a personal task…') : __('Add a task…') }}"
            autocomplete="off"
        >
        <input
            wire:model="dueDate"
            type="date"
            class="project-card__add-date @error('dueDate') is-invalid @enderror"
            aria-label="{{ __('Due date') }}"
        >
        <button type="submit" class="project-card__add-btn" wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('Add') }}</span>
            <span wire:loading>…</span>
        </button>
    </form>
</section>
