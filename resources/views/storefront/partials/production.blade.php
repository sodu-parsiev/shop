<section id="production" class="border-b border-brand-black/10 bg-white py-16 lg:py-28">
    <div class="grid w-full grid-cols-1 items-center gap-12 px-[18px] min-[801px]:pl-0 min-[801px]:pr-[4vw] lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:gap-16">
        <div class="relative aspect-[4/3] w-full overflow-hidden bg-brand-black/5">
            <img
                src="{{ asset('brand/fabric-touch.jpg') }}"
                alt="{{ $homeContent->get('process.eyebrow') }}"
                class="h-full w-full object-cover"
                loading="lazy"
            >
            <span class="absolute bottom-5 left-5 bg-white px-4 py-2 text-xs font-bold uppercase">{{ $homeContent->get('sellers.caption') }}</span>
        </div>

        <div>
            <x-storefront.section-label number="04">{{ $homeContent->get('process.eyebrow') }}</x-storefront.section-label>

            <h2 class="mt-6 text-[42px] leading-[0.98] font-normal sm:text-[64px] lg:text-[78px]">
                {{ $homeContent->get('process.heading') }}<br>
                <span class="text-brand-pink">{{ $homeContent->get('process.subheading') }}</span>
            </h2>

            <p class="mt-5 max-w-xl text-sm leading-relaxed text-brand-black/60">
                Мы разместили производство ближе к хлопковым полям, сократили цепочку и сохранили контроль качества. Это позволяет планировать крупные партии без лишних посредников.
            </p>

            <div data-animate="stagger" class="mt-8 flex flex-wrap items-center gap-x-4 gap-y-4 border-t border-brand-black/10 pt-6">
                @foreach ($homeContent->get('process.stages', []) as $i => $stage)
                    @if ($i > 0)
                        <span aria-hidden="true" class="text-brand-pink">→</span>
                    @endif
                    <div>
                        <p class="text-base font-bold">{{ $stage['title'] }}</p>
                        <p class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">{{ $stage['subtitle'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
