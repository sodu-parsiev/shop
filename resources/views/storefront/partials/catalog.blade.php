<section id="catalog" x-data="catalogFilter" class="border-b border-brand-black/10 bg-brand-black py-16 text-white lg:py-24">
    <div class="px-6 lg:px-16">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <x-storefront.section-label number="02">{{ $homeContent->get('catalog.eyebrow') }}</x-storefront.section-label>
                <h2 class="mt-6 text-3xl font-extrabold sm:text-4xl">
                    {{ $homeContent->get('catalog.heading') }}
                    <span class="text-brand-pink">{{ $homeContent->get('catalog.heading_accent') }}</span>
                </h2>
            </div>
            <p class="text-sm font-bold tracking-wide uppercase text-white/40">{{ $homeContent->get('catalog.count_label') }}</p>
        </div>

        <div class="mt-8 flex flex-wrap gap-2">
            <button
                type="button"
                @click="setAvailability('all')"
                :class="availability === 'all' ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                class="px-4 py-2 text-sm font-bold"
            >{{ $homeContent->get('catalog.availability_all_label') }}</button>
            <button
                type="button"
                @click="setAvailability('stock')"
                :class="availability === 'stock' ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold"
            ><span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>{{ $homeContent->get('catalog.availability_stock_label') }}</button>
            <button
                type="button"
                @click="setAvailability('order')"
                :class="availability === 'order' ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                class="px-4 py-2 text-sm font-bold"
            >{{ $homeContent->get('catalog.availability_order_label') }}</button>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 rounded-2xl bg-white/5 p-4 ring-1 ring-white/10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_category_label') }}</label>
                <select
                    @change="setCategory($event.target.value)"
                    class="mt-1 w-full rounded-lg border-0 bg-transparent px-0 py-1 text-sm text-white ring-0 focus:ring-0"
                >
                    <option value="all" class="text-brand-black">{{ $homeContent->get('catalog.filter_all_label') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" class="text-brand-black">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_density_label') }}</label>
                <select class="mt-1 w-full rounded-lg border-0 bg-transparent px-0 py-1 text-sm text-white ring-0 focus:ring-0">
                    <option class="text-brand-black">{{ $homeContent->get('catalog.filter_manager_label') }}</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_size_label') }}</label>
                <select class="mt-1 w-full rounded-lg border-0 bg-transparent px-0 py-1 text-sm text-white ring-0 focus:ring-0">
                    <option class="text-brand-black">{{ $homeContent->get('catalog.filter_size_grid_label') }}</option>
                </select>
            </div>

            <div x-data="qtyStepper">
                <label class="text-xs font-bold tracking-wide text-white/40 uppercase">{{ $homeContent->get('catalog.filter_qty_label') }}</label>
                <div class="mt-1 flex items-center gap-2">
                    <input
                        type="number"
                        x-model.number="qty"
                        min="5000"
                        step="1000"
                        class="w-full border-0 bg-transparent px-0 py-1 text-sm text-white ring-0 focus:ring-0"
                    >
                    <span class="text-sm text-white/40">{{ $homeContent->get('catalog.qty_unit') }}</span>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div x-data="qtyStepper" class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @click="setPreset(5000, '5000_10000')"
                    :class="qty === 5000 ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                    class="px-4 py-2 text-sm font-bold"
                >5 000 {{ $homeContent->get('catalog.qty_unit') }}</button>
                <button
                    type="button"
                    @click="setPreset(10000, '10000_25000')"
                    :class="qty === 10000 ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                    class="px-4 py-2 text-sm font-bold"
                >10 000 {{ $homeContent->get('catalog.qty_unit') }}</button>
                <button
                    type="button"
                    @click="setPreset(25000, '25000_plus')"
                    :class="qty === 25000 ? 'bg-brand-pink text-white' : 'bg-white/5 text-white ring-1 ring-white/15'"
                    class="px-4 py-2 text-sm font-bold"
                >25 000 {{ $homeContent->get('catalog.qty_unit') }}</button>
            </div>

            <p class="text-xs text-white/40">{{ $homeContent->get('catalog.price_note') }}</p>
        </div>

        <div data-animate="stagger" class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
