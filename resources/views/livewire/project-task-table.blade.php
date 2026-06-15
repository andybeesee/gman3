<section class="task-panel">
    <div class="task-panel__header">
        <h2 class="task-panel__title">{{ __('Tasks') }}</h2>
        <span class="task-panel__count">
            {{ trans_choice(':count task|:count tasks', $tasks->count(), ['count' => $tasks->count()]) }}
        </span>
    </div>

    @if ($tasks->isEmpty())
        <p class="task-empty" wire:key="task-empty">{{ __('No tasks have been added to this project yet.') }}</p>
    @else
        <div class="task-table-wrap" wire:key="task-table">
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
                        @php $taskStatus = $task->status; @endphp
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

    <form
        wire:submit.prevent="addTask"
        class="project-card__add-task"
    >
        <i class="fa-solid fa-plus project-card__add-icon" aria-hidden="true"></i>
        <input
            wire:model="title"
            type="text"
            id="project-task-title-{{ $project->id }}"
            class="project-card__add-input @error('title') is-invalid @enderror"
            placeholder="{{ __('Add a task…') }}"
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

@script
<script>
    $wire.on('project-task-added', () => {
        document.getElementById('project-task-title-{{ $project->id }}').focus();
    });
</script>
@endscript
