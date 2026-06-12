<nav class="task-pagination__nav" aria-label="{{ __('Pagination Navigation') }}">
    <p class="task-pagination__summary">
        {{ __('Showing :from–:to of :total results', [
            'from' => $paginator->firstItem() ?? 0,
            'to' => $paginator->lastItem() ?? 0,
            'total' => $paginator->total(),
        ]) }}
    </p>

    @if ($paginator->hasPages())
        <div class="task-pagination__controls">
            @if ($paginator->onFirstPage())
                <span
                    class="task-pagination__button is-disabled"
                    aria-disabled="true"
                    aria-label="{{ __('Go to first page') }}"
                >
                    <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
                </span>
            @else
                <a
                    href="{{ $paginator->url(1) }}"
                    class="task-pagination__button"
                    aria-label="{{ __('Go to first page') }}"
                >
                    <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
                </a>
            @endif

            @if ($paginator->onFirstPage())
                <span
                    class="task-pagination__button is-disabled"
                    aria-disabled="true"
                    aria-label="{{ __('Go to previous page') }}"
                >
                    <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="task-pagination__button"
                    aria-label="{{ __('Go to previous page') }}"
                >
                    <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
                </a>
            @endif

            <label class="task-pagination__page-select">
                <span class="sr-only">{{ __('Page') }}</span>
                <select data-pagination-page-select aria-label="{{ __('Select page') }}">
                    @for ($page = 1; $page <= $paginator->lastPage(); $page++)
                        <option
                            value="{{ $paginator->url($page) }}"
                            @selected($page === $paginator->currentPage())
                        >
                            {{ __('Page :page', ['page' => $page]) }}
                        </option>
                    @endfor
                </select>
            </label>

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="task-pagination__button"
                    aria-label="{{ __('Go to next page') }}"
                >
                    <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
                </a>
            @else
                <span
                    class="task-pagination__button is-disabled"
                    aria-disabled="true"
                    aria-label="{{ __('Go to next page') }}"
                >
                    <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
                </span>
            @endif

            @if ($paginator->onLastPage())
                <span
                    class="task-pagination__button is-disabled"
                    aria-disabled="true"
                    aria-label="{{ __('Go to last page') }}"
                >
                    <i class="fa-solid fa-angles-right" aria-hidden="true"></i>
                </span>
            @else
                <a
                    href="{{ $paginator->url($paginator->lastPage()) }}"
                    class="task-pagination__button"
                    aria-label="{{ __('Go to last page') }}"
                >
                    <i class="fa-solid fa-angles-right" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    @endif
</nav>
