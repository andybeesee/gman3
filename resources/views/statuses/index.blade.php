<x-layouts.app title="{{ __('Statuses') }}">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Statuses') }}</h1>
        <p class="dashboard-subtitle">{{ __('Manage task and project statuses.') }}</p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('All statuses') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count status|:count statuses', $statuses->count(), ['count' => $statuses->count()]) }}
            </span>
            <a href="{{ route('statuses.create') }}" class="task-form__submit">
                {{ __('New status') }}
            </a>
        </div>

        @if ($statuses->isEmpty())
            <p class="task-empty">{{ __('No statuses have been created yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Order') }}</th>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Slug') }}</th>
                            <th scope="col">{{ __('Icon') }}</th>
                            <th scope="col">{{ __('Color') }}</th>
                            <th scope="col">{{ __('Closed') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statuses as $status)
                            <tr>
                                <td class="task-table__numeric">{{ $status->sort_order }}</td>
                                <td class="task-table__title" title="{{ $status->name }}">
                                    <a href="{{ route('statuses.show', $status) }}" class="task-table__title-link">
                                        <i class="fa-solid {{ $status->fontAwesomeIcon() }}" aria-hidden="true"></i>
                                        {{ $status->name }}
                                    </a>
                                </td>
                                <td>{{ $status->slug }}</td>
                                <td>{{ $status->icon }}</td>
                                <td>{{ $status->color }}</td>
                                <td>
                                    @if ($status->is_closed)
                                        <i class="fa-solid fa-check" aria-label="{{ __('Yes') }}"></i>
                                    @else
                                        <span aria-label="{{ __('No') }}">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
