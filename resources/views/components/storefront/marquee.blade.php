@props(['text' => ''])

<div {{ $attributes->merge(['class' => 'truncate text-center whitespace-nowrap']) }}>
    <span class="text-sm font-bold tracking-wide uppercase">{{ $text }}</span>
</div>
