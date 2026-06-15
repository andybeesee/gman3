@php
    $status = $project->status;
@endphp

<x-layouts.app :title="$project->title">
    <div class="dashboard-header">
        <a href="{{ route('projects.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Projects') }}</span>
        </a>

        <h1 class="dashboard-title">{{ $project->title }}</h1>

        @if ($project->description)
            <p class="dashboard-subtitle">{{ $project->description }}</p>
        @endif

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="project-meta" aria-label="{{ __('Project details') }}">
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
                <dd>{{ $project->isPublic() ? __('Public') : __('Private') }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Ownership') }}</dt>
                <dd>
                    @if ($project->isUserOwned())
                        {{ $project->ownerUser?->name ?? __('Personal') }}
                    @else
                        {{ __('Team') }}
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Teams') }}</dt>
                <dd>
                    @if ($project->teams->isEmpty())
                        <span class="task-table__muted">—</span>
                    @else
                        {{ $project->teams->pluck('name')->join(', ') }}
                    @endif
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Start') }}</dt>
                <dd>{{ $project->start_date?->format('M j, Y') ?? '—' }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Due') }}</dt>
                <dd>{{ $project->due_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <livewire:project-task-table :project="$project" />
</x-layouts.app>
