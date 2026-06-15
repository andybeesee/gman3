<x-layouts.app :title="__('New Status')">
    <div class="dashboard-header">
        <a href="{{ route('statuses.index') }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('Statuses') }}</span>
        </a>

        <h1 class="dashboard-title">{{ __('New Status') }}</h1>
    </div>

    <div class="task-form-wrap">
        <form method="POST" action="{{ route('statuses.store') }}" class="task-form">
            @csrf

            <div class="task-form__field">
                <label for="name" class="task-form__label">
                    {{ __('Name') }}
                    <span class="task-form__required" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="task-form__input @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    maxlength="255"
                    placeholder="{{ __('e.g. In Progress') }}"
                >
                @error('name')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label for="slug" class="task-form__label">
                    {{ __('Slug') }}
                    <span class="task-form__required" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="slug"
                    name="slug"
                    class="task-form__input @error('slug') is-invalid @enderror"
                    value="{{ old('slug') }}"
                    required
                    maxlength="255"
                    placeholder="{{ __('e.g. in-progress') }}"
                >
                @error('slug')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__row">
                <div class="task-form__field">
                    <label for="icon" class="task-form__label">
                        {{ __('Icon') }}
                        <span class="task-form__required" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="icon"
                        name="icon"
                        class="task-form__input @error('icon') is-invalid @enderror"
                        value="{{ old('icon') }}"
                        required
                        maxlength="255"
                        placeholder="{{ __('e.g. circle') }}"
                    >
                    @error('icon')
                        <p class="task-form__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="task-form__field">
                    <label for="color" class="task-form__label">
                        {{ __('Color') }}
                        <span class="task-form__required" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="color"
                        name="color"
                        class="task-form__input @error('color') is-invalid @enderror"
                        required
                    >
                        @foreach (['gray', 'blue', 'green', 'orange', 'red', 'yellow', 'purple', 'pink'] as $color)
                            <option value="{{ $color }}" {{ old('color') === $color ? 'selected' : '' }}>
                                {{ ucfirst($color) }}
                            </option>
                        @endforeach
                    </select>
                    @error('color')
                        <p class="task-form__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="task-form__field">
                <label for="sort_order" class="task-form__label">
                    {{ __('Sort order') }}
                    <span class="task-form__required" aria-hidden="true">*</span>
                </label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    class="task-form__input @error('sort_order') is-invalid @enderror"
                    value="{{ old('sort_order', 0) }}"
                    required
                    min="0"
                >
                @error('sort_order')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__field">
                <label class="task-form__checkbox-label">
                    <input
                        type="checkbox"
                        name="is_closed"
                        value="1"
                        {{ old('is_closed') ? 'checked' : '' }}
                    >
                    <span>{{ __('Marks tasks as closed') }}</span>
                </label>
                @error('is_closed')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__footer">
                <a href="{{ route('statuses.index') }}" class="task-form__cancel">{{ __('Cancel') }}</a>
                <button type="submit" class="task-form__submit">
                    {{ __('Create status') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
