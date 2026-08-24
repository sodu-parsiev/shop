@php
    $inStock = $product->isInStock();
    $colors = $product->colors;
    $densities = $product->densities;
    $sizeCount = $product->sizes->count();
    $densityLabel = $inStock
        ? 'По текущей складской партии'
        : ($densities->pluck('name')->implode(' / ') ?: 'По модели и ТЗ');
    $sizeLabel = $inStock
        ? 'Размерный ряд и остатки — по запросу'
        : ($product->slug === 'full-cycle-custom-production' ? 'Индивидуальная размерная сетка' : 'Размерная сетка по спецификации');
    $coverImage = $product->cover_image ?: asset('brand/catalog-white-v2.jpg');
    $categoryLabel = $product->category?->name ?? $homeContent->get('catalog.kicker');
    $defaultDensity = $densities->first()?->name ?: 'Уточнить с менеджером';
    $defaultSize = $product->sizes->first()?->name ?: $homeContent->get('catalog.filter_size_grid_label');
    $defaultColor = $product->colors->first()?->name ?: 'Уточнить с менеджером';
    $activeColors = $colors->where('is_active', true)->values();
    $activeSizes = $product->sizes->where('is_active', true)->values();
    $activeDensities = $densities->where('is_active', true)->values();
@endphp

<article
    data-category="{{ $product->category_id }}"
    data-in-stock="{{ $inStock ? 'true' : 'false' }}"
    data-colors='@json($product->colors->pluck('id')->map(fn ($id) => (string) $id)->values())'
    data-densities='@json($product->densities->pluck('id')->map(fn ($id) => (string) $id)->values())'
    data-sizes='@json($product->sizes->pluck('id')->map(fn ($id) => (string) $id)->values())'
    data-product-card
    x-show="matches($el)"
    class="flex flex-col overflow-hidden bg-white text-brand-black"
>
    <div class="relative aspect-[4/5] w-full overflow-hidden bg-brand-cream">
        <div class="absolute left-4 top-4 z-10">
            @if ($inStock)
                <x-storefront.pill-badge tone="green">{{ $homeContent->get('catalog.badge_in_stock') }}</x-storefront.pill-badge>
            @else
                <x-storefront.pill-badge tone="pink">{{ $homeContent->get('catalog.badge_made_to_order') }}</x-storefront.pill-badge>
            @endif
        </div>

        <img
            src="{{ $coverImage }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-cover"
            loading="lazy"
        >
    </div>

    <div class="flex flex-1 flex-col gap-4 p-5">
        <div class="flex items-center justify-between text-xs font-bold tracking-wide text-brand-black/40 uppercase">
            <span>{{ $categoryLabel }}</span>
            <span>{{ $homeContent->get('catalog.moq_prefix') }} {{ number_format($product->moq, 0, ',', ' ') }} {{ $homeContent->get('catalog.qty_unit') }}</span>
        </div>

        <h3 class="text-2xl font-normal leading-tight">
            <a href="{{ $product->publicUrl() }}" class="hover:text-brand-pink">{{ $product->name }}</a>
        </h3>
        <p class="text-sm text-brand-black/60">{{ str($product->short_description ?: $product->description)->limit(140) }}</p>

        <dl class="mt-2 divide-y divide-brand-black/10 border-y border-brand-black/10 text-sm">
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-brand-black/50">{{ $homeContent->get('catalog.density_label') }}</dt>
                <dd class="text-right font-semibold">{{ $densityLabel }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-brand-black/50">{{ $homeContent->get('catalog.sizes_label') }}</dt>
                <dd class="text-right font-semibold">{{ $sizeCount > 0 ? $sizeLabel : $homeContent->get('catalog.filter_size_grid_label') }}</dd>
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

        <div class="mt-auto flex items-end justify-between gap-3 pt-2">
            <div>
                <p class="text-xs text-brand-black/50">{{ $homeContent->get('catalog.price_label') }}</p>
                <p class="text-2xl font-normal leading-none">{{ $homeContent->get('catalog.price_value') }}</p>
                <p class="text-xs text-brand-black/40">{{ $homeContent->get('catalog.price_note_small') }}</p>
                <a href="{{ $product->publicUrl() }}" class="mt-3 inline-block text-xs font-bold text-brand-pink">Подробнее</a>
            </div>
            <button
                type="button"
                @click="$store.orderBuilder.addProduct({
                    id: {{ $product->id }},
                    name: @js($product->name),
                    category: @js($categoryLabel),
                    availability: @js($inStock ? 'На складе' : 'Под заказ'),
                    moq: {{ $product->moq }},
                    image: @js($coverImage),
                    densities: [selectedOptionLabel('density', @js($defaultDensity))],
                    sizes: [selectedOptionLabel('size', @js($defaultSize))],
                    colors: [selectedOptionLabel('color', @js($defaultColor))],
                    availableColors: @js($activeColors->pluck('name')),
                    availableSizes: @js($activeSizes->pluck('name')),
                    availableDensities: @js($activeDensities->pluck('name')),
                    colorSwatches: @js($activeColors->pluck('hex_code', 'name')),
                })"
                class="inline-flex shrink-0 items-center gap-2 bg-brand-pink px-4 py-3 text-xs font-bold text-white sm:text-sm"
            >
                {{ $inStock ? $homeContent->get('catalog.cta_stock') : $homeContent->get('catalog.cta_made_to_order') }}
                <span aria-hidden="true">&#8599;&#65038;</span>
            </button>
        </div>
    </div>
</article>
