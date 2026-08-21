@php
    $filterLabels = [
        'category' => $categories->pluck('name', 'id')->all(),
        'color' => $colors->pluck('name', 'id')->all(),
        'density' => $densities->pluck('name', 'id')->all(),
        'size' => $sizes->pluck('name', 'id')->all(),
    ];
@endphp

<section id="catalog" x-data='catalogFilter(@json(['labels' => $filterLabels, 'total' => $products->count()], JSON_UNESCAPED_UNICODE))' class="border-b border-brand-black/10 bg-brand-black py-16 text-white lg:py-28">
    <div class="storefront-shell">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <x-storefront.section-label number="02">{{ $homeContent->get('catalog.eyebrow') }}</x-storefront.section-label>
                <h2 class="mt-6 max-w-[980px] text-[42px] leading-[0.96] font-normal sm:text-[72px] lg:text-[86px]">
                    {{ $homeContent->get('catalog.heading') }}
                    <span class="text-brand-pink">{{ $homeContent->get('catalog.heading_accent') }}</span>
                </h2>
            </div>
            <p class="text-sm font-bold tracking-wide uppercase text-white/40" x-text="visibleCountLabel()">{{ $homeContent->get('catalog.count_label') }}</p>
        </div>

        <div class="mt-10 flex flex-wrap gap-2">
            <button
                type="button"
                @click="setFilter('availability', 'all')"
                :class="availability === 'all' ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                class="px-5 py-3 text-sm font-bold"
            >{{ $homeContent->get('catalog.availability_all_label') }}</button>
            <button
                type="button"
                @click="setFilter('availability', 'stock')"
                :class="availability === 'stock' ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold"
            ><span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>{{ $homeContent->get('catalog.availability_stock_label') }}</button>
            <button
                type="button"
                @click="setFilter('availability', 'order')"
                :class="availability === 'order' ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                class="px-5 py-3 text-sm font-bold"
            >{{ $homeContent->get('catalog.availability_order_label') }}</button>
        </div>

        <div class="mt-8 grid grid-cols-1 border-y border-white/20 sm:grid-cols-2 lg:grid-cols-5">
            <div class="border-b border-white/20 p-4 sm:border-r lg:border-b-0">
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_category_label') }}</label>
                <select
                    x-model="category"
                    @change="setFilter('category', $event.target.value)"
                    class="mt-2 w-full border-0 bg-transparent px-0 py-1 text-sm font-bold text-white ring-0 focus:ring-0"
                >
                    <option value="all" class="text-brand-black">{{ $homeContent->get('catalog.filter_all_label') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" class="text-brand-black">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="border-b border-white/20 p-4 lg:border-r lg:border-b-0">
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">Цвет</label>
                <select
                    x-model="color"
                    @change="setFilter('color', $event.target.value)"
                    class="mt-2 w-full border-0 bg-transparent px-0 py-1 text-sm font-bold text-white ring-0 focus:ring-0"
                >
                    <option value="all" class="text-brand-black">{{ $homeContent->get('catalog.filter_all_label') }}</option>
                    @foreach ($colors as $color)
                        <option value="{{ $color->id }}" class="text-brand-black">{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="border-b border-white/20 p-4 lg:border-r lg:border-b-0">
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_density_label') }}</label>
                <select
                    x-model="density"
                    @change="setFilter('density', $event.target.value)"
                    class="mt-2 w-full border-0 bg-transparent px-0 py-1 text-sm font-bold text-white ring-0 focus:ring-0"
                >
                    <option value="all" class="text-brand-black">{{ $homeContent->get('catalog.filter_all_label') }}</option>
                    @foreach ($densities as $density)
                        <option value="{{ $density->id }}" class="text-brand-black">{{ $density->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="border-b border-white/20 p-4 sm:border-r sm:border-b-0">
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_size_label') }}</label>
                <select
                    x-model="size"
                    @change="setFilter('size', $event.target.value)"
                    class="mt-2 w-full border-0 bg-transparent px-0 py-1 text-sm font-bold text-white ring-0 focus:ring-0"
                >
                    <option value="all" class="text-brand-black">{{ $homeContent->get('catalog.filter_all_label') }}</option>
                    @foreach ($sizes as $size)
                        <option value="{{ $size->id }}" class="text-brand-black">{{ $size->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="p-4">
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_qty_label') }}</label>
                <div class="mt-2 flex items-center gap-2">
                    <input
                        type="number"
                        x-model.number="$store.orderBuilder.quantity"
                        @change="$store.orderBuilder.setQuantity($event.target.value)"
                        min="5000"
                        step="1000"
                        class="w-full border-0 bg-transparent px-0 py-1 text-sm font-bold text-white ring-0 focus:ring-0"
                    >
                    <span class="text-sm text-white/40">{{ $homeContent->get('catalog.qty_unit') }}</span>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @click="$store.orderBuilder.setPreset(5000, '5000_10000')"
                    :class="$store.orderBuilder.quantity === 5000 ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                    class="px-5 py-3 text-sm font-bold"
                >5 000 {{ $homeContent->get('catalog.qty_unit') }}</button>
                <button
                    type="button"
                    @click="$store.orderBuilder.setPreset(10000, '10000_25000')"
                    :class="$store.orderBuilder.quantity === 10000 ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                    class="px-5 py-3 text-sm font-bold"
                >10 000 {{ $homeContent->get('catalog.qty_unit') }}</button>
                <button
                    type="button"
                    @click="$store.orderBuilder.setPreset(25000, '25000_plus')"
                    :class="$store.orderBuilder.quantity === 25000 ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                    class="px-5 py-3 text-sm font-bold"
                >25 000 {{ $homeContent->get('catalog.qty_unit') }}</button>
            </div>

            <p class="text-xs text-white/40">{{ $homeContent->get('catalog.price_note') }}</p>
        </div>

        <div data-animate="stagger" class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div x-show="visibleCount === 0" x-cloak class="mt-8 border border-white/15 bg-white/5 p-6 text-sm text-white/70">
            <p class="text-xl font-normal text-white">По выбранным фильтрам ничего не найдено.</p>
            <button type="button" class="mt-4 bg-white px-5 py-3 text-sm font-bold text-brand-black" @click="reset()">Сбросить фильтры</button>
        </div>
    </div>
</section>
