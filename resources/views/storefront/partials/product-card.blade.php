@php
    $inStock = $product->isInStock();
    $colors = $product->distinctColors();
    $densities = $product->distinctDensities();
    $sizeCount = $product->variants->pluck('size_id')->unique()->count();
@endphp

<article
    data-category="{{ $product->category_id }}"
    x-show="matches({{ $product->category_id }}, {{ $inStock ? 'true' : 'false' }})"
    class="flex flex-col overflow-hidden rounded-2xl bg-white text-brand-black"
>
    <div class="relative aspect-[3/4] w-full overflow-hidden bg-brand-black/5">
        <div class="absolute top-3 left-3 z-10">
            @if ($inStock)
                <x-storefront.pill-badge tone="green">{{ $homeContent->get('catalog.badge_in_stock') }}</x-storefront.pill-badge>
            @else
                <x-storefront.pill-badge tone="pink">{{ $homeContent->get('catalog.badge_made_to_order') }}</x-storefront.pill-badge>
            @endif
        </div>

        <img
            src="{{ $product->cover_image }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-cover"
            loading="lazy"
        >
    </div>

    <div class="flex flex-1 flex-col gap-3 p-5">
        <div class="flex items-center justify-between text-xs font-bold tracking-wide text-brand-black/40 uppercase">
            <span>{{ $homeContent->get('catalog.kicker') }}</span>
            <span>{{ $homeContent->get('catalog.moq_prefix') }} {{ number_format($product->moq, 0, ',', ' ') }} {{ $homeContent->get('catalog.qty_unit') }}</span>
        </div>

        <h3 class="text-lg font-extrabold">{{ $product->name }}</h3>
        <p class="text-sm text-brand-black/60">{{ $product->short_description }}</p>

        <dl class="mt-2 space-y-1 border-t border-brand-black/10 pt-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-brand-black/50">{{ $homeContent->get('catalog.density_label') }}</dt>
                <dd class="text-right font-semibold">{{ $densities->pluck('name')->implode(' / ') }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-brand-black/50">{{ $homeContent->get('catalog.sizes_label') }}</dt>
                <dd class="text-right font-semibold">{{ $sizeCount }} {{ $homeContent->get('catalog.sizes_unit') }}</dd>
            </div>
        </dl>

        @if ($colors->isNotEmpty())
            <div class="flex flex-wrap gap-x-3 gap-y-1">
                @foreach ($colors as $color)
                    <span class="inline-flex items-center gap-1.5 text-xs text-brand-black/70">
                        <span
                            @class([
                                'h-2.5 w-2.5 rounded-full ring-1 ring-brand-black/20',
                            ])
                            style="background-color: {{ $color->hex_code ?? 'transparent' }}"
                        ></span>
                        {{ $color->name }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="mt-auto flex items-end justify-between gap-3 border-t border-brand-black/10 pt-3">
            <div>
                <p class="text-xs text-brand-black/50">{{ $homeContent->get('catalog.price_label') }}</p>
                <p class="text-base font-extrabold">{{ $homeContent->get('catalog.price_value') }}</p>
                <p class="text-xs text-brand-black/40">{{ $homeContent->get('catalog.price_note_small') }}</p>
            </div>
            <a href="#apply" class="inline-flex shrink-0 items-center gap-2 bg-brand-pink px-4 py-2 text-sm font-bold text-white">
                {{ $inStock ? $homeContent->get('catalog.cta_stock') : $homeContent->get('catalog.cta_made_to_order') }}
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </div>
</article>
