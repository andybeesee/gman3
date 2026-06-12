@props(['paginator'])

@if ($paginator->total() > 0)
    <div {{ $attributes->class(['task-pagination']) }}>
        {!! $paginator->links('pagination.tasks') !!}
    </div>
@endif
