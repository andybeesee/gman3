@props(['tasks', 'statuses'])

<div class="task-table-wrap">
    <table class="task-table">
        <thead>
            <tr>
                <th scope="col">{{ __('Title') }}</th>
                <th scope="col">{{ __('Project') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Teams') }}</th>
                <th scope="col">{{ __('Assignees') }}</th>
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
                    $status = $task->status;
                @endphp
                <tr>
                    <td class="task-table__title">
                        <span class="task-table__title-content">
                            <span class="task-table__title-text" title="{{ $task->title }}">
                                {{ $task->title }}
                            </span>
                            <x-visibility-indicator :resource="$task" />
                        </span>
                    </td>
                    <td class="task-table__project">
                        @if ($task->isProjectOwned() && $task->owner)
                            <a href="{{ route('projects.show', $task->owner) }}" class="task-table__title-link">
                                {{ $task->owner->title }}
                            </a>
                        @else
                            <span class="task-table__muted">—</span>
                        @endif
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
                    <td class="task-table__teams">
                        @if ($task->teams->isEmpty())
                            <span class="task-table__muted">—</span>
                        @else
                            {{ $task->teams->pluck('name')->join(', ') }}
                        @endif
                    </td>
                    <td class="task-table__assignees">
                        @if ($task->assignees->isEmpty())
                            <span class="task-table__muted">—</span>
                        @else
                            {{ $task->assignees->pluck('name')->join(', ') }}
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
