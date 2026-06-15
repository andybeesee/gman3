@php
    $status = $task->status;
@endphp

<x-layouts.app :title="$task->title">
    <div class="dashboard-header">
        <a href="{{ route('tasks.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Tasks') }}</span>
        </a>

        <h1 class="dashboard-title">{{ $task->title }}</h1>

        @if ($task->description)
            <p class="dashboard-subtitle">{{ $task->description }}</p>
        @endif

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="project-meta" aria-label="{{ __('Task details') }}">
        <dl class="project-meta__list">
            <div class="project-meta__item">
                <dt>{{ __('Status') }}</dt>
                <dd>
                    @if ($status)
                        <span
                            class="task-status status-color-{{ $status->color }}"
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
                <dt>{{ __('Visibility') }}</dt>
                <dd>
                    <span class="task-detail__visibility">
                        @if ($task->isPublic())
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            {{ __('Public') }}
                        @else
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            {{ __('Private') }}
                        @endif
                    </span>
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Owner') }}</dt>
                <dd>
                    @if ($task->isProjectOwned() && $task->owner)
                        <a href="{{ route('projects.show', $task->owner) }}" class="task-table__title-link">
                            {{ $task->owner->title }}
                        </a>
                    @elseif ($task->isTeamOwned() && $task->owner)
                        <a href="{{ route('teams.show', $task->owner) }}" class="task-table__title-link">
                            {{ $task->owner->name }}
                        </a>
                    @elseif ($task->isUserOwned() && $task->owner)
                        <a href="{{ route('users.show', $task->owner) }}" class="task-table__title-link">
                            {{ $task->owner->name }}
                        </a>
                    @else
                        <span class="task-table__muted">—</span>
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Assignees') }}</dt>
                <dd>
                    @if ($task->assignees->isEmpty())
                        <span class="task-table__muted">—</span>
                    @else
                        {{ $task->assignees->pluck('name')->join(', ') }}
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Teams') }}</dt>
                <dd>
                    @if ($task->teams->isEmpty())
                        <span class="task-table__muted">—</span>
                    @else
                        {{ $task->teams->pluck('name')->join(', ') }}
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Start') }}</dt>
                <dd>{{ $task->start_date?->format('M j, Y') ?? '—' }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Due') }}</dt>
                <dd>{{ $task->due_date?->format('M j, Y') ?? '—' }}</dd>
            </div>

            @if ($task->completed_at)
                <div class="project-meta__item">
                    <dt>{{ __('Completed') }}</dt>
                    <dd>
                        {{ $task->completed_at->format('M j, Y') }}
                        @if ($task->completedBy)
                            {{ __('by :name', ['name' => $task->completedBy->name]) }}
                        @endif
                    </dd>
                </div>
            @endif

            @if ($task->checklist)
                <div class="project-meta__item">
                    <dt>{{ __('Checklist') }}</dt>
                    <dd>
                        <a href="{{ route('checklists.show', $task->checklist) }}" class="task-table__title-link">
                            {{ $task->checklist->title }}
                        </a>
                    </dd>
                </div>
            @endif
        </dl>
    </section>

    <div class="task-detail__actions">
        @can('updateStatus', $task)
            <div class="task-detail__status-panel">
                <h2 class="task-detail__section-title">{{ __('Set status') }}</h2>
                <div class="task-detail__status-list">
                    @foreach ($statuses as $statusOption)
                        <form method="POST" action="{{ route('tasks.status.update', $task) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status_id" value="{{ $statusOption->id }}">
                            <button
                                type="submit"
                                class="task-detail__status-btn status-color-{{ $statusOption->color }}"
                                @disabled($task->status?->id === $statusOption->id)
                            >
                                <i class="fa-solid {{ $statusOption->fontAwesomeIcon() }}" aria-hidden="true"></i>
                                <span>{{ $statusOption->name }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endcan
    </div>
</x-layouts.app>
