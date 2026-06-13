@php
    use App\Enums\TeamRole;
@endphp

<section class="task-panel" data-team-members>
    <div class="task-panel__header">
        <h2 class="task-panel__title">{{ __('Team members') }}</h2>
        <span class="task-panel__count" data-team-members-count>
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

                <select name="role" class="team-members-add__select" aria-label="{{ __('Role') }}">
                    @foreach (TeamRole::cases() as $teamRole)
                        <option value="{{ $teamRole->value }}" @selected(old('role', TeamRole::Member->value) === $teamRole->value)>
                            {{ $teamRole->label() }}
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

            @error('role')
                <p class="team-members-add__error">{{ $message }}</p>
            @enderror
        </form>
    @endif

    <p class="team-members-add__error" data-team-members-panel-error hidden></p>

    @if ($members->isEmpty())
        <p class="task-empty" data-team-members-empty>{{ __('No members yet.') }}</p>
    @else
        <p class="task-empty" data-team-members-empty hidden>{{ __('No members yet.') }}</p>
    @endif

    @if ($members->isNotEmpty())
        <div class="task-table-wrap" data-team-members-table>
            <table class="task-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Email') }}</th>
                        <th scope="col">{{ __('Role') }}</th>
                        <th scope="col">{{ __('Open tasks here') }}</th>
                        @if ($canUpdateMembers)
                            <th scope="col" class="task-table__actions-heading">
                                <span class="sr-only">{{ __('Actions') }}</span>
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody data-team-members-list>
                    @foreach ($members as $member)
                        <tr data-team-member-row data-member-id="{{ $member->id }}">
                            <td class="task-table__title" title="{{ $member->name }}">
                                {{ $member->name }}
                            </td>
                            <td class="task-table__email">
                                {{ $member->email }}
                            </td>
                            <td>
                                @if ($canUpdateMembers)
                                    <select
                                        name="role"
                                        class="team-members-add__select team-member-role-form__select"
                                        aria-label="{{ __('Role for :name', ['name' => $member->name]) }}"
                                        data-team-member-role
                                        data-url="{{ route('teams.members.role.update', [$team, $member]) }}"
                                    >
                                        @foreach (TeamRole::cases() as $teamRole)
                                            <option
                                                value="{{ $teamRole->value }}"
                                                @selected($member->pivot->role === $teamRole)
                                            >
                                                {{ $teamRole->label() }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <p class="team-members-add__error" data-team-member-error hidden></p>
                                @else
                                    {{ $member->pivot->role?->label() ?? TeamRole::Member->label() }}
                                @endif
                            </td>
                            <td class="task-table__numeric">
                                {{ number_format($member->open_team_tasks_count) }}
                            </td>
                            @if ($canUpdateMembers)
                                <td class="task-table__actions">
                                    <button
                                        type="button"
                                        class="team-member-list__remove"
                                        data-team-member-remove
                                        data-url="{{ route('teams.members.destroy', [$team, $member]) }}"
                                    >
                                        {{ __('Remove') }}
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
