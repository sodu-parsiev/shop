@php
    $toAsset = static function (?string $path): string {
        if (! $path) {
            return asset('brand/catalog-white-v2.jpg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    };

    $coverImage = $toAsset($product->cover_image);
    $gallery = collect([
        ['url' => $coverImage, 'alt' => $product->name],
    ])->merge(
        $product->images->map(fn ($image): array => [
            'url' => $toAsset($image->path),
            'alt' => $image->alt_text ?: $product->name,
        ])
    )->unique('url')->values();
    $categoryLabel = $product->category?->name ?? 'Каталог';
    $availabilityLabel = $product->isInStock() ? 'На складе' : 'Под заказ';
    $densityLabel = $product->densities->pluck('name')->implode(', ') ?: 'По модели и ТЗ';
    $sizeLabel = $product->sizes->pluck('name')->implode(', ') ?: 'По спецификации';
    $colorLabel = $product->colors->pluck('name')->implode(', ') ?: 'По ТЗ';
    $activeColors = $product->colors->where('is_active', true)->values();
    $activeSizes = $product->sizes->where('is_active', true)->values();
    $activeDensities = $product->densities->where('is_active', true)->values();
@endphp

<x-layouts.storefront
    :title="$seoMeta['title']"
    :home-content="$homeContent"
    :seo-meta="$seoMeta"
    :schema-graph="$schemaGraph"
>
    <main>
        @include('storefront.partials.topbar')
        @include('storefront.partials.header')

        <section
            x-data="{ activeImage: @js($gallery->first()['url']), zoomOpen: false, selectedColors: [], selectedSizes: [], selectedDensities: [] }"
            x-init="storefrontAnalytics.track('product_view', { product_id: {{ $product->id }}, product_name: @js($product->name), category: @js($categoryLabel) })"
            class="bg-white py-10 text-brand-black lg:py-16"
        >
            <div class="storefront-shell">
                <nav class="flex flex-wrap gap-2 text-xs font-bold tracking-wide text-brand-black/45 uppercase">
                    <a href="{{ route('home') }}" class="hover:text-brand-pink">Главная</a>
                    <span>/</span>
                    <a href="{{ route('home') }}#catalog" class="hover:text-brand-pink">Каталог</a>
                    <span>/</span>
                    <span>{{ $product->name }}</span>
                </nav>

                <div class="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.82fr)] lg:gap-14">
                    <div>
                        <button type="button" class="block w-full overflow-hidden bg-brand-cream" @click="zoomOpen = true">
                            <img :src="activeImage" alt="{{ $product->name }}" class="aspect-[4/5] w-full object-cover">
                        </button>

                        @if ($gallery->count() > 1)
                            <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-6">
                                @foreach ($gallery as $image)
                                    <button type="button" class="aspect-square overflow-hidden bg-brand-cream ring-1 ring-brand-black/10" @click="activeImage = @js($image['url'])">
                                        <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="h-full w-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="lg:pt-4">
                        <div class="flex flex-wrap gap-2">
                            <x-storefront.pill-badge :tone="$product->isInStock() ? 'green' : 'pink'">{{ $availabilityLabel }}</x-storefront.pill-badge>
                            <x-storefront.pill-badge tone="dark">{{ $categoryLabel }}</x-storefront.pill-badge>
                        </div>

                        <h1 class="mt-6 max-w-2xl text-[42px] leading-[0.96] font-normal sm:text-[64px] lg:text-[78px]">
                            {{ $product->h1 ?: $product->name }}
                        </h1>

                        @if ($product->short_description || $product->description)
                            <p class="mt-6 max-w-xl text-base leading-relaxed text-brand-black/65">
                                {{ $product->short_description ?: $product->description }}
                            </p>
                        @endif

                        <dl class="mt-8 divide-y divide-brand-black/10 border-y border-brand-black/10 text-sm">
                            <div class="grid gap-2 py-4 sm:grid-cols-[160px_1fr]">
                                <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">MOQ</dt>
                                <dd class="font-bold">{{ number_format($product->moq, 0, ',', ' ') }} шт.</dd>
                            </div>
                            <div class="grid gap-2 py-4 sm:grid-cols-[160px_1fr]">
                                <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">Состав</dt>
                                <dd class="font-bold">{{ $product->composition ?: 'По спецификации' }}</dd>
                            </div>
                            <div class="grid gap-2 py-4 sm:grid-cols-[160px_1fr]">
                                <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">Плотность</dt>
                                <dd class="font-bold">{{ $densityLabel }}</dd>
                            </div>
                            <div class="grid gap-2 py-4 sm:grid-cols-[160px_1fr]">
                                <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">Размеры</dt>
                                <dd class="font-bold">{{ $sizeLabel }}</dd>
                            </div>
                            <div class="grid gap-2 py-4 sm:grid-cols-[160px_1fr]">
                                <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">Цвета</dt>
                                <dd class="font-bold">{{ $colorLabel }}</dd>
                            </div>
                            <div class="grid gap-2 py-4 sm:grid-cols-[160px_1fr]">
                                <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">Сроки / остатки</dt>
                                <dd class="font-bold">{{ $product->stock_conditions ?: 'Уточняет менеджер после заявки' }}</dd>
                            </div>
                        </dl>

                        @if ($activeColors->isNotEmpty() || $activeSizes->isNotEmpty() || $activeDensities->isNotEmpty())
                            <div class="mt-8">
                                <p class="text-sm text-brand-black/60">
                                    Отметьте цвет, размер и плотность, если это важно — можно выбрать несколько вариантов или оставить пусто.
                                </p>
                                <div class="mt-3 grid grid-cols-1 gap-5 sm:grid-cols-3">
                                    @if ($activeColors->isNotEmpty())
                                        <x-storefront.attribute-checkbox-group label="Цвет" model="selectedColors" :options="$activeColors" />
                                    @endif

                                    @if ($activeSizes->isNotEmpty())
                                        <x-storefront.attribute-checkbox-group label="Размер" model="selectedSizes" :options="$activeSizes" />
                                    @endif

                                    @if ($activeDensities->isNotEmpty())
                                        <x-storefront.attribute-checkbox-group label="Плотность" model="selectedDensities" :options="$activeDensities" />
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <button
                                type="button"
                                @click="$store.orderBuilder.addProduct({
                                    id: {{ $product->id }},
                                    name: @js($product->name),
                                    category: @js($categoryLabel),
                                    availability: @js($availabilityLabel),
                                    moq: {{ $product->moq }},
                                    image: @js($coverImage),
                                    colors: selectedColors,
                                    sizes: selectedSizes,
                                    densities: selectedDensities,
                                    availableColors: @js($activeColors->pluck('name')),
                                    availableSizes: @js($activeSizes->pluck('name')),
                                    availableDensities: @js($activeDensities->pluck('name')),
                                    colorSwatches: @js($activeColors->pluck('hex_code', 'name')),
                                })"
                                class="inline-flex items-center justify-between bg-brand-pink px-6 py-4 text-sm font-bold text-white"
                            >
                                {{ $product->isInStock() ? 'Запросить остатки' : 'Рассчитать пошив' }}
                                <span aria-hidden="true">&#8599;&#65038;</span>
                            </button>
                            <a href="#contacts" class="inline-flex items-center justify-center border border-brand-black px-6 py-4 text-sm font-bold">
                                Перейти к форме
                            </a>
                        </div>
                    </div>
                </div>

                @if ($product->description)
                    <div class="mt-14 grid grid-cols-1 gap-8 border-t border-brand-black/10 pt-10 lg:grid-cols-[0.45fr_1fr]">
                        <h2 class="text-3xl font-normal">Описание</h2>
                        <p class="max-w-3xl text-base leading-relaxed text-brand-black/65">{{ $product->description }}</p>
                    </div>
                @endif

                @if (filled($product->size_table))
                    <div class="mt-12 border-t border-brand-black/10 pt-10">
                        <h2 class="text-3xl font-normal">Размерная таблица</h2>
                        <div class="mt-5 overflow-x-auto">
                            <table class="min-w-full border-y border-brand-black/10 text-left text-sm">
                                <thead class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">
                                    <tr>
                                        <th class="py-3 pr-4">Размер</th>
                                        <th class="py-3 pr-4">Ширина</th>
                                        <th class="py-3 pr-4">Длина</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-black/10">
                                    @foreach ($product->size_table as $row)
                                        <tr>
                                            <td class="py-3 pr-4 font-bold">{{ $row['size'] ?? '' }}</td>
                                            <td class="py-3 pr-4">{{ $row['chest'] ?? '' }}</td>
                                            <td class="py-3 pr-4">{{ $row['length'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($product->customizationServices->isNotEmpty())
                    <div class="mt-12 border-t border-brand-black/10 pt-10">
                        <h2 class="text-3xl font-normal">Кастомизация</h2>
                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($product->customizationServices as $service)
                                <article class="border border-brand-black/10 p-5">
                                    <h3 class="font-bold">{{ $service->name }}</h3>
                                    @if ($service->description)
                                        <p class="mt-2 text-sm leading-relaxed text-brand-black/60">{{ $service->description }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($relatedProducts->isNotEmpty())
                    <div class="mt-12 border-t border-brand-black/10 pt-10">
                        <h2 class="text-3xl font-normal">Другие модели</h2>
                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            @foreach ($relatedProducts as $related)
                                <a href="{{ $related->publicUrl() }}" class="group block border border-brand-black/10 p-5 hover:border-brand-pink">
                                    <span class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">{{ $related->category?->name }}</span>
                                    <span class="mt-3 block text-xl font-normal group-hover:text-brand-pink">{{ $related->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div x-show="zoomOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click="zoomOpen = false" @keydown.escape.window="zoomOpen = false">
                <img :src="activeImage" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
            </div>
        </section>

        @include('storefront.partials.cta-form')
    </main>

    @include('storefront.partials.order-drawer')
    @include('storefront.partials.footer')
</x-layouts.storefront>
