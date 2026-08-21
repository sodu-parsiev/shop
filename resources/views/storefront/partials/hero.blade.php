<section id="top" data-animate="hero" class="border-b border-brand-black/10 bg-white">
    <div class="storefront-shell grid grid-cols-1 items-center gap-12 py-10 sm:py-16 lg:grid-cols-[minmax(0,1fr)_minmax(430px,0.86fr)] lg:gap-20 lg:py-14">
        <div>
            <div class="flex flex-wrap gap-2">
                <x-storefront.pill-badge tone="outline">{{ $homeContent->get('hero.tag_production') }}</x-storefront.pill-badge>
                <x-storefront.pill-badge tone="outline">{{ $homeContent->get('hero.tag_b2b') }}</x-storefront.pill-badge>
            </div>

            <h1 class="mt-6 max-w-[620px] text-[54px] leading-[0.88] font-black sm:text-[76px] lg:text-[96px]">
                {{ $homeContent->get('hero.headline_main') }}
                <span class="block text-brand-pink">{{ $homeContent->get('hero.headline_accent') }}</span>
            </h1>

            <p class="mt-6 max-w-[640px] text-lg leading-relaxed text-brand-black">
                {{ $homeContent->get('hero.subcopy') }}
            </p>

            <div class="mt-6 flex max-w-[660px] items-center gap-3 border-l-4 border-brand-pink bg-brand-pink/10 px-5 py-4 text-sm font-bold">
                <span class="h-3 w-3 shrink-0 rounded-full bg-emerald-700" aria-hidden="true"></span>
                <span>{{ $homeContent->get('hero.callout') }}</span>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#catalog" class="inline-flex items-center justify-between gap-8 bg-brand-black px-6 py-4 text-sm font-bold text-white">
                    {{ $homeContent->get('hero.cta_primary') }}
                    <span aria-hidden="true">&#8599;&#65038;</span>
                </a>
                <a href="#contacts" class="inline-flex items-center justify-between gap-8 border border-brand-black px-6 py-4 text-sm font-bold">
                    {{ $homeContent->get('hero.cta_secondary') }}
                </a>
            </div>

            <div class="mt-6 grid grid-cols-1 divide-y divide-brand-black/10 border-y border-brand-black/10 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                @foreach ($homeContent->get('hero.stats', []) as $stat)
                    <x-storefront.stat :value="$stat['value']" :label="$stat['label']" class="py-4 sm:px-6 sm:first:pl-0" />
                @endforeach
            </div>
        </div>

        <div class="relative">
            <div class="aspect-[3/4] w-full overflow-hidden rounded-tl-[96px] bg-brand-black/5 sm:rounded-tl-[150px]">
                <img
                    data-hero-photo
                    src="{{ asset('brand/model-motion.jpg') }}"
                    alt="{{ $homeContent->get('hero.headline_main') }} {{ $homeContent->get('hero.headline_accent') }}"
                    class="h-full w-full object-cover"
                    loading="lazy"
                >
            </div>

            <div class="absolute right-0 top-6 bg-brand-pink px-5 py-4 text-white shadow-lg sm:px-7 sm:py-5">
                <p class="text-2xl font-extrabold sm:text-3xl">{{ $homeContent->get('hero.hero_badge_value') }}</p>
                <p class="text-xs font-bold tracking-wide opacity-90">{{ $homeContent->get('hero.hero_badge_label') }}</p>
            </div>

            <div class="absolute bottom-6 left-6 flex items-center gap-3 bg-white px-4 py-3 shadow-lg sm:px-5">
                <img src="{{ asset('brand/mark.png') }}" alt="" class="h-11 w-11">
                <span class="text-xs font-bold uppercase leading-tight">
                    От сырья<br>
                    <strong>до готовой партии</strong>
                </span>
            </div>
        </div>
    </div>

    <div class="bg-brand-pink py-4 text-white">
        <div class="storefront-shell">
            <x-storefront.marquee :text="$homeContent->get('hero.bottom_ticker')" />
        </div>
    </div>
</section>
