@props(['value', 'label'])

<div {{ $attributes->merge(['class' => '']) }}>
    <p class="text-lg font-extrabold">{{ $value }}</p>
    <p class="mt-1 text-xs font-bold tracking-wide text-current/60 uppercase">{{ $label }}</p>
</div>
