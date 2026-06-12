<x-layouts.app title="{{ __('Teams') }}">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Teams') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('All teams across the organization.') }}
        </p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('All teams') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count team|:count teams', $teams->total(), ['count' => $teams->total()]) }}
            </span>
        </div>

        @if ($teams->isEmpty())
            <p class="task-empty">{{ __('No teams have been created yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Team') }}</th>
                            <th scope="col">{{ __('Members') }}</th>
                            <th scope="col">{{ __('Open tasks') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teams as $team)
                            <tr>
                                <td class="task-table__title" title="{{ $team->name }}">
                                    <a href="{{ route('teams.show', $team) }}" class="task-table__title-link">
                                        {{ $team->name }}
                                    </a>
                                </td>
                                <td class="task-table__numeric">
                                    {{ number_format($team->members_count) }}
                                </td>
                                <td class="task-table__numeric">
                                    {{ number_format($team->open_tasks_count) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if (! $teams->isEmpty())
        <x-task-pagination :paginator="$teams" />
    @endif
</x-layouts.app>
