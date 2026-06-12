@props(['task', 'statuses'])

<div class="task-actions" data-task-actions>
    <button
        type="button"
        class="task-actions__toggle"
        data-task-actions-toggle
        aria-haspopup="menu"
        aria-expanded="false"
        aria-label="{{ __('Actions for :task', ['task' => $task->title]) }}"
    >
        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
    </button>

    <div class="task-actions__menu" data-task-actions-menu role="menu">
        <a href="#" class="task-actions__item" role="menuitem">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
            <span>{{ __('View') }}</span>
        </a>
        <a href="#" class="task-actions__item" role="menuitem">
            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
            <span>{{ __('Edit') }}</span>
        </a>

        @can('updateStatus', $task)
            <div class="task-actions__separator" role="separator"></div>
            <div class="task-actions__label">{{ __('Set status') }}</div>

            @foreach ($statuses as $status)
                <form
                    method="POST"
                    action="{{ route('tasks.status.update', $task) }}"
                    role="none"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_id" value="{{ $status->id }}">
                    <button
                        type="submit"
                        class="task-actions__item"
                        role="menuitem"
                        @disabled($task->status?->id === $status->id)
                    >
                        <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
                        <span>{{ $status->name }}</span>
                    </button>
                </form>
            @endforeach
        @endcan

        <div class="task-actions__separator" role="separator"></div>

        <button type="button" class="task-actions__item task-actions__item--danger" role="menuitem">
            <i class="fa-solid fa-trash" aria-hidden="true"></i>
            <span>{{ __('Delete') }}</span>
        </button>
    </div>
</div>
