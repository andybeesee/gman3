@props(['project'])

@if ($project->isUserOwned())
    <span>{{ $project->ownerUser?->name ?? __('Personal') }}</span>
@elseif ($project->teams->isEmpty())
    <span class="task-table__muted">{{ __('Team') }}</span>
@else
    <span>{{ $project->teams->pluck('name')->join(', ') }}</span>
@endif
