<x-layouts.app :title="__('New Task')">
    <div class="dashboard-header">
        <a href="{{ route('tasks.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Tasks') }}</span>
        </a>

        <h1 class="dashboard-title">{{ __('New Task') }}</h1>
    </div>

    <div class="task-form-wrap">
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

            <div class="task-form__field task-form__field--wide">
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

            <div class="task-form__footer">
                <a href="{{ route('tasks.index') }}" class="task-form__cancel">{{ __('Cancel') }}</a>
                <button type="submit" class="task-form__submit">
                    {{ __('Create task') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
