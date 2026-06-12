<x-layouts.app title="{{ __('Projects') }}">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Projects') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('Projects you can access across the organization.') }}
        </p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('All projects') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count project|:count projects', $projects->total(), ['count' => $projects->total()]) }}
            </span>
        </div>

        @if ($projects->isEmpty())
            <p class="task-empty">{{ __('No projects are available to you yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Title') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Ownership') }}</th>
                            <th scope="col">{{ __('Teams') }}</th>
                            <th scope="col">{{ __('Tasks') }}</th>
                            <th scope="col">{{ __('Start') }}</th>
                            <th scope="col">{{ __('Due') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            @php
                                $status = $project->status;
                            @endphp
                            <tr>
                                <td class="task-table__title" title="{{ $project->title }}">
                                    <a href="{{ route('projects.show', $project) }}" class="task-table__title-link">
                                        {{ $project->title }}
                                    </a>
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
                                <td>
                                    @if ($project->isPersonallyOwned())
                                        <span>{{ $project->ownerUser?->name ?? __('Personal') }}</span>
                                    @else
                                        <span>{{ __('Team') }}</span>
                                    @endif
                                </td>
                                <td class="task-table__teams">
                                    @if ($project->teams->isEmpty())
                                        <span class="task-table__muted">—</span>
                                    @else
                                        {{ $project->teams->pluck('name')->join(', ') }}
                                    @endif
                                </td>
                                <td class="task-table__numeric">
                                    {{ number_format($project->owned_tasks_count) }}
                                </td>
                                <td class="task-table__date">
                                    {{ $project->start_date?->format('M j, Y') ?? '—' }}
                                </td>
                                <td class="task-table__date">
                                    {{ $project->due_date?->format('M j, Y') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if (! $projects->isEmpty())
        <x-task-pagination :paginator="$projects" />
    @endif
</x-layouts.app>
