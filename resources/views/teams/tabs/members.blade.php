<section class="task-panel">
    <div class="task-panel__header">
        <h2 class="task-panel__title">{{ __('Team members') }}</h2>
        <span class="task-panel__count">
            {{ trans_choice(':count member|:count members', $members->count(), ['count' => $members->count()]) }}
        </span>
    </div>

    @if ($canUpdateMembers)
        <form method="POST" action="{{ route('teams.members.update', $team) }}" class="team-members-form">
            @csrf
            @method('PUT')

            <fieldset class="team-members-form__fieldset">
                <legend class="team-members-form__legend">{{ __('Select members for this team') }}</legend>

                @if ($users->isEmpty())
                    <p class="task-empty">{{ __('No users are available to add.') }}</p>
                @else
                    <ul class="team-members-form__list">
                        @foreach ($users as $user)
                            <li class="team-members-form__item">
                                <label class="team-members-form__label">
                                    <input
                                        type="checkbox"
                                        name="user_ids[]"
                                        value="{{ $user->id }}"
                                        @checked($members->contains('id', $user->id))
                                    >
                                    <span>{{ $user->name }}</span>
                                    <span class="team-members-form__email">{{ $user->email }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </fieldset>

            @if ($users->isNotEmpty())
                <div class="team-members-form__actions">
                    <button type="submit" class="team-members-form__submit">
                        {{ __('Save members') }}
                    </button>
                </div>
            @endif
        </form>
    @else
        @if ($members->isEmpty())
            <p class="task-empty">{{ __('No members yet.') }}</p>
        @else
            <ul class="team-member-list">
                @foreach ($members as $member)
                    <li class="team-member-list__item">
                        <span>{{ $member->name }}</span>
                        <span class="team-members-form__email">{{ $member->email }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</section>
