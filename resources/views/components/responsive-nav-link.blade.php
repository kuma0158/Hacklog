@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-2 border-accent text-start text-sm font-medium text-accent-deep bg-accent-soft focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-2 border-transparent text-start text-sm font-medium text-muted hover:text-ink hover:bg-app-2 hover:border-line-strong focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
