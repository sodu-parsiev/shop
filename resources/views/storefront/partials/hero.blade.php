<section data-animate="hero-pin" class="border-b border-brand-black/10 bg-brand-cream">
    <div class="grid grid-cols-1 items-center gap-10 px-6 py-16 lg:grid-cols-2 lg:px-16 lg:py-24">
        <div>
            <div class="flex flex-wrap gap-2">
                <x-storefront.pill-badge tone="outline">{{ $homeContent->get('hero.tag_production') }}</x-storefront.pill-badge>
                <x-storefront.pill-badge tone="outline">{{ $homeContent->get('hero.tag_b2b') }}</x-storefront.pill-badge>
            </div>

            <h1 class="mt-6 text-5xl leading-[0.95] font-extrabold sm:text-6xl">
                {{ $homeContent->get('hero.headline_main') }}
                <span class="text-brand-pink">{{ $homeContent->get('hero.headline_accent') }}</span>
            </h1>

            <p class="mt-6 max-w-lg text-base text-brand-black/70">
                {{ $homeContent->get('hero.subcopy') }}
            </p>

            <div class="mt-6 max-w-lg border-l-4 border-brand-pink bg-brand-pink/10 px-4 py-3 text-sm font-semibold">
                {{ $homeContent->get('hero.callout') }}
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#catalog" class="inline-flex items-center gap-2 bg-brand-black px-6 py-3 text-sm font-bold text-white">
                    {{ $homeContent->get('hero.cta_primary') }}
                    <span aria-hidden="true">↗</span>
                </a>
                <a href="#apply" class="inline-flex items-center gap-2 border border-brand-black px-6 py-3 text-sm font-bold">
                    {{ $homeContent->get('hero.cta_secondary') }}
                </a>
            </div>

            <div data-animate="stagger" class="mt-10 grid grid-cols-3 gap-6 border-t border-brand-black/10 pt-6">
                @foreach ($homeContent->get('hero.stats', []) as $stat)
                    <x-storefront.stat :value="$stat['value']" :label="$stat['label']" />
                @endforeach
            </div>
        </div>

        <div class="relative">
            <div class="aspect-[3/4] w-full overflow-hidden rounded-tl-[150px] bg-brand-black/5">
                <img
                    data-hero-photo
                    src="https://placehold.co/900x1200/1a1512/f7f3ec?text=Свой+Ход"
                    alt="{{ $homeContent->get('hero.headline_main') }} {{ $homeContent->get('hero.headline_accent') }}"
                    class="h-full w-full object-cover"
                    loading="lazy"
                >
            </div>

            <div class="absolute top-6 right-0 bg-brand-pink px-5 py-4 text-white shadow-lg">
                <p class="text-2xl font-extrabold">{{ $homeContent->get('hero.hero_badge_value') }}</p>
                <p class="text-xs font-bold tracking-wide uppercase opacity-90">{{ $homeContent->get('hero.hero_badge_label') }}</p>
            </div>
        </div>
    </div>

    <div class="border-t border-brand-black/10 bg-brand-black py-3 text-white">
        <div class="px-6 lg:px-16">
            <x-storefront.marquee :text="$homeContent->get('hero.bottom_ticker')" />
        </div>
    </div>
</section>
