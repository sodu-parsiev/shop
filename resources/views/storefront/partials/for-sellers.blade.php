<section class="border-b border-brand-black/10 bg-brand-pink py-16 text-white lg:py-24">
    <div class="grid grid-cols-1 gap-10 px-6 lg:grid-cols-2 lg:px-16">
        <div>
            <x-storefront.section-label number="03">{{ $homeContent->get('sellers.eyebrow') }}</x-storefront.section-label>

            <h2 class="mt-6 text-3xl font-extrabold sm:text-4xl">
                {{ $homeContent->get('sellers.heading') }}<br>
                {{ $homeContent->get('sellers.subheading') }}
            </h2>

            <a href="#apply" class="mt-6 inline-flex items-center gap-2 border-b border-white pb-1 text-sm font-bold">
                {{ $homeContent->get('sellers.cta') }}
                <span aria-hidden="true">↗</span>
            </a>
        </div>

        <div>
            <ol data-animate="stagger" class="divide-y divide-white/20 border-y border-white/20">
                @foreach ($homeContent->get('sellers.items', []) as $i => $item)
                    <li class="flex items-center justify-between gap-4 py-4">
                        <span class="flex items-center gap-4 text-base font-bold">
                            <span class="text-white/50">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $item['label'] }}
                        </span>
                        <span aria-hidden="true" class="text-white/50">→</span>
                    </li>
                @endforeach
            </ol>

            <p class="mt-4 text-xs font-bold tracking-wide text-white/60 uppercase">{{ $homeContent->get('sellers.caption') }}</p>
        </div>
    </div>
</section>
