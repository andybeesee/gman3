<x-layouts.app title="{{ __('Users') }}">
    <div class="dashboard-header">
        <h1 class="dashboard-title">{{ __('Users') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('People in your organization and reporting line.') }}
        </p>

        @if (session('status'))
            <p class="dashboard-flash" role="status">{{ session('status') }}</p>
        @endif
    </div>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('All users') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count user|:count users', $users->total(), ['count' => $users->total()]) }}
            </span>
        </div>

        @if ($users->isEmpty())
            <p class="task-empty">{{ __('No users are visible to you yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Email') }}</th>
                            <th scope="col">{{ __('Reports') }}</th>
                            <th scope="col">{{ __('Open tasks') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="task-table__title" title="{{ $user->name }}">
                                    <a href="{{ route('users.show', $user) }}" class="task-table__title-link">
                                        {{ $user->name }}
                                    </a>
                                </td>
                                <td class="task-table__email">
                                    {{ $user->email }}
                                </td>
                                <td class="task-table__numeric">
                                    {{ number_format($user->subordinates_count) }}
                                </td>
                                <td class="task-table__numeric">
                                    {{ number_format($user->open_tasks_count) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if (! $users->isEmpty())
        <x-task-pagination :paginator="$users" />
    @endif
</x-layouts.app>
