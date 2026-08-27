@props(['label', 'model', 'options'])

<div>
    <p class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">{{ $label }}</p>
    <div class="mt-2 flex flex-wrap gap-2">
        @foreach ($options as $option)
            <label
                class="inline-flex cursor-pointer items-center gap-1.5 border px-3 py-1.5 text-xs font-bold transition-colors"
                :class="{{ $model }}.includes(@js($option->name)) ? 'border-brand-black bg-brand-black text-white' : 'border-brand-black/15 text-brand-black/70 hover:border-brand-black/40'"
            >
                <input type="checkbox" value="{{ $option->name }}" x-model="{{ $model }}" class="sr-only">
                <span x-show="{{ $model }}.includes(@js($option->name))" aria-hidden="true">&#10003;</span>
                @if ($option->hex_code ?? null)
                    <span class="h-3 w-3 shrink-0 rounded-full ring-1 ring-brand-black/20" style="background-color: {{ $option->hex_code }};"></span>
                @endif
                {{ $option->name }}
            </label>
        @endforeach
    </div>
</div>
