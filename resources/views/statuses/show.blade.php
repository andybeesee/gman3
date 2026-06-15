<x-layouts.app :title="$status->name">
    <div class="dashboard-header">
        <a href="{{ route('statuses.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Statuses') }}</span>
        </a>

        <h1 class="dashboard-title">
            <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
            {{ $status->name }}
        </h1>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Details') }}</h2>
            <a href="{{ route('statuses.edit', $status) }}" class="task-form__submit">
                {{ __('Edit') }}
            </a>
        </div>

        <dl class="task-meta">
            <div class="task-meta__row">
                <dt class="task-meta__label">{{ __('Name') }}</dt>
                <dd class="task-meta__value">{{ $status->name }}</dd>
            </div>
            <div class="task-meta__row">
                <dt class="task-meta__label">{{ __('Slug') }}</dt>
                <dd class="task-meta__value">{{ $status->slug }}</dd>
            </div>
            <div class="task-meta__row">
                <dt class="task-meta__label">{{ __('Icon') }}</dt>
                <dd class="task-meta__value">
                    <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
                    {{ $status->icon }}
                </dd>
            </div>
            <div class="task-meta__row">
                <dt class="task-meta__label">{{ __('Color') }}</dt>
                <dd class="task-meta__value">{{ $status->color }}</dd>
            </div>
            <div class="task-meta__row">
                <dt class="task-meta__label">{{ __('Sort order') }}</dt>
                <dd class="task-meta__value">{{ $status->sort_order }}</dd>
            </div>
            <div class="task-meta__row">
                <dt class="task-meta__label">{{ __('Closed') }}</dt>
                <dd class="task-meta__value">{{ $status->is_closed ? __('Yes') : __('No') }}</dd>
            </div>
        </dl>
    </section>
</x-layouts.app>
