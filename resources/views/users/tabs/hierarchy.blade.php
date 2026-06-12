<div class="hierarchy-tab">
    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Supervisor') }}</h2>
        </div>

        @if ($user->supervisor)
            <div class="hierarchy-supervisor">
                <a href="{{ route('users.show', $user->supervisor) }}" class="task-table__title-link">
                    {{ $user->supervisor->name }}
                </a>
                <span class="task-table__email">{{ $user->supervisor->email }}</span>
            </div>
        @else
            <p class="task-empty">{{ __('No supervisor assigned.') }}</p>
        @endif
    </section>

    <section class="task-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Supervises') }}</h2>
            <span class="task-panel__count">
                {{ trans_choice(':count person|:count people', $supervisedUsers->count(), ['count' => $supervisedUsers->count()]) }}
            </span>
        </div>

        @if ($supervisedUsers->isEmpty())
            <p class="task-empty">{{ __('No one reports to this user yet.') }}</p>
        @else
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Email') }}</th>
                            <th scope="col" class="task-table__actions-heading">
                                <span class="sr-only">{{ __('Actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supervisedUsers as $supervisedUser)
                            <tr>
                                <td class="task-table__title" title="{{ $supervisedUser->name }}">
                                    <a href="{{ route('users.show', $supervisedUser) }}" class="task-table__title-link">
                                        {{ $supervisedUser->name }}
                                    </a>
                                </td>
                                <td class="task-table__email">
                                    {{ $supervisedUser->email }}
                                </td>
                                <td class="task-table__actions">
                                    <form
                                        method="POST"
                                        action="{{ route('users.subordinates.destroy', [$user, $supervisedUser]) }}"
                                        class="team-member-list__remove-form"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="team-member-list__remove">
                                            {{ __('Remove') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
