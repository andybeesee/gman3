<x-layouts.app :title="$status->name">
    <div class="dashboard-header">
        <a href="{{ route('statuses.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Statuses') }}</span>
        </a>

        <h1 class="dashboard-title">
            <span class="task-status status-color-{{ $status->color }}">
                <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
                <span>{{ $status->name }}</span>
            </span>
        </h1>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="project-meta" aria-label="{{ __('Status details') }}">
        <dl class="project-meta__list">
            <div class="project-meta__item">
                <dt>{{ __('Name') }}</dt>
                <dd>{{ $status->name }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Slug') }}</dt>
                <dd><code>{{ $status->slug }}</code></dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Icon') }}</dt>
                <dd>
                    <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
                    <code>{{ $status->icon }}</code>
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Color') }}</dt>
                <dd>
                    <span class="task-status status-color-{{ $status->color }}">
                        <i class="fa-solid fa-circle" aria-hidden="true"></i>
                        <span>{{ ucfirst($status->color) }}</span>
                    </span>
                </dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Sort order') }}</dt>
                <dd>{{ $status->sort_order }}</dd>
            </div>

            <div class="project-meta__item">
                <dt>{{ __('Closed') }}</dt>
                <dd>
                    @if ($status->is_closed)
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        {{ __('Yes — marks tasks as closed') }}
                    @else
                        <span class="task-table__muted">{{ __('No') }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    <div class="task-detail__actions">
        <div class="task-detail__status-panel">
            <h2 class="task-detail__section-title">{{ __('Actions') }}</h2>
            <div class="task-detail__status-list">
                <a href="{{ route('statuses.edit', $status) }}" class="task-detail__status-btn">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>{{ __('Edit status') }}</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
