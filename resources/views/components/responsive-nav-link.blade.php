@props(['active' => false])

<a {{ $attributes->merge(['class' => 'dropdown-item' . ($active ? ' active' : '')]) }}>
    {{ $slot }}
</a>
