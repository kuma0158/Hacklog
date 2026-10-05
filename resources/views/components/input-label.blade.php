@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-medium text-muted mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
