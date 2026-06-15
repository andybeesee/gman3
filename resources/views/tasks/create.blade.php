<x-layouts.app :title="__('New Task')">
    <div class="dashboard-header">
        <a href="{{ route('tasks.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Tasks') }}</span>
        </a>

        <h1 class="dashboard-title">{{ __('New Task') }}</h1>
        <p class="dashboard-subtitle">
            {{ __('Create a task and place it with the right owner, teams, and assignees.') }}
        </p>
    </div>

    <div class="task-form-wrap task-form-wrap--wide">
        <form method="POST" action="{{ route('tasks.store') }}" class="task-form">
            @csrf

            <div class="task-form__field">
                <label for="title" class="task-form__label">
                    {{ __('Title') }}
                    <span class="task-form__required" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="task-form__input @error('title') is-invalid @enderror"
                    value="{{ old('title') }}"
                    required
                    autofocus
                    maxlength="255"
                    placeholder="{{ __('Task title') }}"
                >
                @error('title')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="description" class="task-form__label">{{ __('Description') }}</label>
                <textarea
                    id="description"
                    name="description"
                    class="task-form__textarea @error('description') is-invalid @enderror"
                    rows="4"
                    placeholder="{{ __('Optional description') }}"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__row">
                <div class="task-form__field">
                    <label for="start_date" class="task-form__label">{{ __('Start date') }}</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        class="task-form__input @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date') }}"
                    >
                    @error('start_date')
                        <p class="task-form__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="task-form__field">
                    <label for="due_date" class="task-form__label">{{ __('Due date') }}</label>
                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        class="task-form__input @error('due_date') is-invalid @enderror"
                        value="{{ old('due_date') }}"
                    >
                    @error('due_date')
                        <p class="task-form__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="task-form__field">
                <label class="task-form__label">{{ __('Visibility') }}</label>
                <div class="task-form__radio-group">
                    <label class="task-form__radio-label">
                        <input
                            type="radio"
                            name="visibility"
                            value="private"
                            {{ old('visibility', 'private') === 'private' ? 'checked' : '' }}
                        >
                        <span class="task-form__radio-text">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            {{ __('Private') }}
                        </span>
                    </label>
                    <label class="task-form__radio-label">
                        <input
                            type="radio"
                            name="visibility"
                            value="public"
                            {{ old('visibility') === 'public' ? 'checked' : '' }}
                        >
                        <span class="task-form__radio-text">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            {{ __('Public') }}
                        </span>
                    </label>
                </div>
                @error('visibility')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="owner_type" class="task-form__label">{{ __('Owner type') }}</label>
                <select id="owner_type" name="owner_type" class="task-form__input @error('owner_type') is-invalid @enderror">
                    <option value="user" @selected(old('owner_type', 'user') === 'user')>{{ __('Me') }}</option>
                    <option value="team" @selected(old('owner_type') === 'team')>{{ __('Team') }}</option>
                    <option value="project" @selected(old('owner_type') === 'project')>{{ __('Project') }}</option>
                </select>
                @error('owner_type')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__row">
                <div class="task-form__field">
                    <label for="owner_team_id" class="task-form__label">{{ __('Team owner') }}</label>
                    <select id="owner_team_id" name="owner_team_id" class="task-form__input @error('owner_team_id') is-invalid @enderror">
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
                    <select id="owner_project_id" name="owner_project_id" class="task-form__input @error('owner_project_id') is-invalid @enderror">
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

            <div class="task-form__footer">
                <a href="{{ route('tasks.index') }}" class="task-form__cancel">{{ __('Cancel') }}</a>
                <button type="submit" class="task-form__submit">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    <span>{{ __('Create task') }}</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
