
@props([
    'title',
    'description' => null,
    'icon' => null,
])
<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 md:flex-row md:items-center md:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-bold flex items-center gap-2 text-gray-800 dark:text-gray-100 m-0">
            @if($icon)
                <i class="{{ $icon }} text-gray-500 dark:text-gray-400"></i>
            @endif
            {{ $title }}
        </h1>
        @if($description)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $description }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2">
        {{ $slot }}
    </div>
</div>

