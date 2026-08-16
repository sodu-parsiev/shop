@props(['number'])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-current/30 text-xs font-bold">
        {{ $number }}
    </span>
    <span class="text-xs font-bold tracking-widest uppercase">{{ $slot }}</span>
</div>
