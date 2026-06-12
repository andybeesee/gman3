<section class="task-panel">
    <div class="task-panel__header">
        <h2 class="task-panel__title">{{ __('Team members') }}</h2>
        <span class="task-panel__count">
            {{ trans_choice(':count member|:count members', $members->count(), ['count' => $members->count()]) }}
        </span>
    </div>

    @if ($canUpdateMembers && $nonMembers->isNotEmpty())
        <form method="POST" action="{{ route('teams.members.store', $team) }}" class="team-members-add">
            @csrf

            <label for="team-member-add" class="team-members-add__label">{{ __('Add member') }}</label>

            <div class="team-members-add__controls">
                <select name="user_id" id="team-member-add" class="team-members-add__select" required>
                    <option value="" disabled selected>{{ __('Select a user') }}</option>
                    @foreach ($nonMembers as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="team-members-add__submit">
                    {{ __('Add') }}
                </button>
            </div>

            @error('user_id')
                <p class="team-members-add__error">{{ $message }}</p>
            @enderror
        </form>
    @endif

    @if ($members->isEmpty())
        <p class="task-empty">{{ __('No members yet.') }}</p>
    @else
        <div class="task-table-wrap">
            <table class="task-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Email') }}</th>
                        <th scope="col">{{ __('Open tasks here') }}</th>
                        @if ($canUpdateMembers)
                            <th scope="col" class="task-table__actions-heading">
                                <span class="sr-only">{{ __('Actions') }}</span>
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td class="task-table__title" title="{{ $member->name }}">
                                {{ $member->name }}
                            </td>
                            <td class="task-table__email">
                                {{ $member->email }}
                            </td>
                            <td class="task-table__numeric">
                                {{ number_format($member->open_team_tasks_count) }}
                            </td>
                            @if ($canUpdateMembers)
                                <td class="task-table__actions">
                                    <form
                                        method="POST"
                                        action="{{ route('teams.members.destroy', [$team, $member]) }}"
                                        class="team-member-list__remove-form"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="team-member-list__remove">
                                            {{ __('Remove') }}
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
