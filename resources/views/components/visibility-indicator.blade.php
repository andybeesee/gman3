@props(['resource'])

@php
    $isPublic = $resource->isPublic();
    $icon = $isPublic ? 'fa-globe' : 'fa-lock';
    $label = $isPublic ? __('Public') : __('Private');

    $sharedWith = $isPublic
        ? collect()
        : $resource->visibilityGrants
            ->map(fn ($grant) => $grant->grantee)
            ->filter()
            ->map(function ($grantee): string {
                $name = $grantee->name ?? __('Unknown');

                return match ($grantee::class) {
                    \App\Models\Team::class => __('Team: :name', ['name' => $name]),
                    \App\Models\User::class => __('User: :name', ['name' => $name]),
                    default => $name,
                };
            })
            ->sort()
            ->values();

    $tooltip = $isPublic
        ? __('Public to everyone in the company.')
        : ($sharedWith->isEmpty()
            ? __('Private. No explicit shares.')
            : __('Private. Shared with: :shared_with', ['shared_with' => $sharedWith->join(', ')]));
@endphp

<span
    class="visibility-indicator visibility-indicator--{{ $isPublic ? 'public' : 'private' }}"
    title="{{ $tooltip }}"
    aria-label="{{ $tooltip }}"
>
    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
    <span class="sr-only">{{ $label }}</span>
</span>
