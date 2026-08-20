<section class="border-b border-brand-black/10 bg-brand-pink py-16 text-white lg:py-28">
    <div class="mx-auto grid max-w-[1324px] grid-cols-1 gap-12 px-5 sm:px-8 lg:grid-cols-2 lg:px-0">
        <div>
            <x-storefront.section-label number="03">{{ $homeContent->get('sellers.eyebrow') }}</x-storefront.section-label>

            <h2 class="mt-8 text-[44px] leading-[0.95] font-normal sm:text-[72px] lg:text-[84px]">
                {{ $homeContent->get('sellers.heading') }}<br>
                <span class="text-brand-black">{{ $homeContent->get('sellers.subheading') }}</span>
            </h2>

            <p class="mt-5 max-w-md text-sm text-white/80">
                Помогаем подготовить крупную партию к выходу на площадку: согласуем размерную матрицу, упаковку, этикетки и маркировку.
            </p>

            <a href="#contacts" class="mt-8 inline-flex items-center gap-2 border-b border-white pb-1 text-sm font-bold">
                {{ $homeContent->get('sellers.cta') }}
                <span aria-hidden="true">&#8599;&#65038;</span>
            </a>
        </div>

        <div>
            <ol data-animate="stagger" class="divide-y divide-white/25 border-y border-white/25">
                @foreach ($homeContent->get('sellers.items', []) as $i => $item)
                    <li class="flex items-center justify-between gap-4 py-6">
                        <span class="flex items-center gap-5 text-base font-bold">
                            <span class="text-white/50">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $item['label'] }}
                        </span>
                        <span aria-hidden="true" class="text-white/50">→</span>
                    </li>
                @endforeach
            </ol>

            <p class="mt-6 text-xs font-bold tracking-wide text-white/60 uppercase">{{ $homeContent->get('sellers.caption') }}</p>
        </div>
    </div>
</section>
