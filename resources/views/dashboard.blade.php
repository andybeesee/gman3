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

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('My tasks') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count open task|:count open tasks', $tasks->total(), ['count' => $tasks->total()]) }}
            </span>
        </div>

        @if ($tasks->isEmpty())
            <p class="task-empty">{{ __('No tasks are assigned to you yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Title') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
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
                                <td class="task-table__title" title="{{ $task->title }}">
                                    {{ $task->title }}
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

            @if ($tasks->hasPages())
                <div class="task-panel__pagination">
                    {{ $tasks->links() }}
                </div>
            @endif
        @endif
    </section>
</x-layouts.app>
