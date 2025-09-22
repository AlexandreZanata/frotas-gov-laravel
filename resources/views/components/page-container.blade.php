@props([
    'pad' => 'py-12',
    'max' => 'max-w-7xl',
    'innerClass' => ''
])
<div {{ $attributes->merge(['class' => $pad]) }}>
    <div class="{{ $max }} mx-auto sm:px-6 lg:px-8 {{ $innerClass }}">
        {{ $slot }}
    </div>
</div>

