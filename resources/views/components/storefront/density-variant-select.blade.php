@props(['product', 'model'])

<div>
    <p class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">Плотность</p>
    <select x-model.number="{{ $model }}" class="mt-1 w-full border border-brand-black/15 px-3 py-2 text-sm font-bold">
        @foreach ($product->densities as $density)
            <option value="{{ $density->id }}">{{ $density->name }}</option>
        @endforeach
    </select>
</div>
