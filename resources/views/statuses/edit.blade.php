<x-layouts.app :title="__('Edit :name', ['name' => $status->name])">
    <div class="dashboard-header">
        <a href="{{ route('statuses.show', $status) }}" class="project-back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ $status->name }}</span>
        </a>

        <h1 class="dashboard-title">{{ __('Edit status') }}</h1>
    </div>

    <div class="task-form-wrap">
        <form method="POST" action="{{ route('statuses.update', $status) }}" class="task-form">
            @csrf
            @method('PATCH')

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
                    value="{{ old('name', $status->name) }}"
                    required
                    autofocus
                    maxlength="255"
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
                    value="{{ old('slug', $status->slug) }}"
                    required
                    maxlength="255"
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
                        value="{{ old('icon', $status->icon) }}"
                        required
                        maxlength="255"
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
                            <option value="{{ $color }}" {{ old('color', $status->color) === $color ? 'selected' : '' }}>
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
                    value="{{ old('sort_order', $status->sort_order) }}"
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
                        {{ old('is_closed', $status->is_closed) ? 'checked' : '' }}
                    >
                    <span>{{ __('Marks tasks as closed') }}</span>
                </label>
                @error('is_closed')
                    <p class="task-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="task-form__footer">
                <a href="{{ route('statuses.show', $status) }}" class="task-form__cancel">{{ __('Cancel') }}</a>
                <button type="submit" class="task-form__submit">
                    {{ __('Save changes') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
