<x-layouts.app title="{{ __('Checklists') }}">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Checklists') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('Ordered task lists you can access across the organization.') }}
        </p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('All checklists') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count checklist|:count checklists', $checklists->total(), ['count' => $checklists->total()]) }}
            </span>
        </div>

        @if ($checklists->isEmpty())
            <p class="task-empty">{{ __('No checklists are available to you yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Title') }}</th>
                            <th scope="col">{{ __('Owner') }}</th>
                            <th scope="col">{{ __('Tasks') }}</th>
                            <th scope="col">{{ __('Start') }}</th>
                            <th scope="col">{{ __('Due') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($checklists as $checklist)
                            <tr>
                                <td class="task-table__title">
                                    <span class="task-table__title-content">
                                        <span class="task-table__title-text" title="{{ $checklist->title }}">
                                            {{ $checklist->title }}
                                        </span>
                                        <x-visibility-indicator :resource="$checklist" />
                                    </span>
                                </td>
                                <td class="task-table__teams">
                                    @if ($checklist->owner instanceof \App\Models\User)
                                        <span class="task-team-chip">{{ $checklist->owner->name }}</span>
                                    @elseif ($checklist->owner instanceof \App\Models\Team)
                                        <span class="task-team-chip">{{ $checklist->owner->name }}</span>
                                    @elseif ($checklist->owner instanceof \App\Models\Project)
                                        <span class="task-team-chip">{{ $checklist->owner->title }}</span>
                                    @else
                                        <span class="task-table__muted">—</span>
                                    @endif
                                </td>
                                <td class="task-table__numeric">
                                    {{ number_format($checklist->tasks_count) }}
                                </td>
                                <td class="task-table__date">
                                    {{ $checklist->start_date?->format('M j, Y') ?? '—' }}
                                </td>
                                <td class="task-table__date">
                                    {{ $checklist->due_date?->format('M j, Y') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if (! $checklists->isEmpty())
        <x-task-pagination :paginator="$checklists" />
    @endif
</x-layouts.app>
