@props(['tone' => 'dark'])

@php
    $toneClasses = match ($tone) {
        'pink' => 'bg-brand-pink text-white',
        'green' => 'bg-emerald-600 text-white',
        'outline' => 'border border-brand-black/20 text-brand-black',
        'outline-light' => 'border border-white/30 text-white',
        default => 'bg-brand-black text-white',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 text-xs font-bold tracking-wide uppercase $toneClasses"]) }}>
    {{ $slot }}
</span>
