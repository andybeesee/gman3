<x-layouts.app title="{{ __('New task') }}">
    <div class="dashboard-header">
        <a href="{{ route('tasks.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Tasks') }}</span>
        </a>

        <h1 class="dashboard-title">{{ __('New task') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('Create a task and place it with the right owner, teams, and assignees.') }}
        </p>
    </div>

    <section class="task-panel task-form-panel">
        <div class="task-panel__header">
            <h2 class="task-panel__title">{{ __('Task details') }}</h2>
        </div>

        <form method="POST" action="{{ route('tasks.store') }}" class="task-form">
            @csrf

            <div class="task-form__field task-form__field--wide">
                <label for="title" class="task-form__label">{{ __('Title') }}</label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title') }}"
                    class="task-form__control"
                    required
                    autofocus
                >
                @error('title')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field task-form__field--wide">
                <label for="description" class="task-form__label">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="4" class="task-form__control">{{ old('description') }}</textarea>
                @error('description')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="start_date" class="task-form__label">{{ __('Start date') }}</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" class="task-form__control">
                @error('start_date')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="due_date" class="task-form__label">{{ __('Due date') }}</label>
                <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" class="task-form__control">
                @error('due_date')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="visibility" class="task-form__label">{{ __('Visibility') }}</label>
                <select id="visibility" name="visibility" class="task-form__control">
                    <option value="private" @selected(old('visibility', 'private') === 'private')>{{ __('Private') }}</option>
                    <option value="public" @selected(old('visibility') === 'public')>{{ __('Public') }}</option>
                </select>
                @error('visibility')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="owner_type" class="task-form__label">{{ __('Owner type') }}</label>
                <select id="owner_type" name="owner_type" class="task-form__control">
                    <option value="user" @selected(old('owner_type', 'user') === 'user')>{{ __('Me') }}</option>
                    <option value="team" @selected(old('owner_type') === 'team')>{{ __('Team') }}</option>
                    <option value="project" @selected(old('owner_type') === 'project')>{{ __('Project') }}</option>
                </select>
                @error('owner_type')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="owner_team_id" class="task-form__label">{{ __('Team owner') }}</label>
                <select id="owner_team_id" name="owner_team_id" class="task-form__control">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected((int) old('owner_team_id') === $team->id)>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
                @error('owner_team_id')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="owner_project_id" class="task-form__label">{{ __('Project owner') }}</label>
                <select id="owner_project_id" name="owner_project_id" class="task-form__control">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((int) old('owner_project_id') === $project->id)>
                            {{ $project->title }}
                        </option>
                    @endforeach
                </select>
                @error('owner_project_id')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label id="assignee_ids-multi-select-label" for="assignee_ids" class="task-form__label">{{ __('Assignees') }}</label>
                <select
                    id="assignee_ids"
                    name="assignee_ids[]"
                    class="task-form__control"
                    multiple
                    data-multi-select
                    data-placeholder="{{ __('Choose assignees') }}"
                    data-search-label="{{ __('Search assignees') }}"
                    data-empty-label="{{ __('No assignees match') }}"
                >
                    @foreach ($assignees as $assignee)
                        <option value="{{ $assignee->id }}" @selected(in_array($assignee->id, old('assignee_ids', [auth()->id()]), false))>
                            {{ $assignee->name }}
                        </option>
                    @endforeach
                </select>
                @error('assignee_ids')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label id="team_ids-multi-select-label" for="team_ids" class="task-form__label">{{ __('Teams') }}</label>
                <select
                    id="team_ids"
                    name="team_ids[]"
                    class="task-form__control"
                    multiple
                    data-multi-select
                    data-placeholder="{{ __('Choose teams') }}"
                    data-search-label="{{ __('Search teams') }}"
                    data-empty-label="{{ __('No teams match') }}"
                >
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected(in_array($team->id, old('team_ids', []), false))>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
                @error('team_ids')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__actions">
                <a href="{{ route('tasks.index') }}" class="task-form__secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="task-form__submit">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    <span>{{ __('Create task') }}</span>
                </button>
            </div>
        </form>
    </section>
</x-layouts.app>
