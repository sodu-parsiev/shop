<section class="border-b border-brand-black/10 bg-brand-cream py-16 lg:py-28">
    <div class="mx-auto max-w-[1324px] px-5 sm:px-8 lg:px-0">
        <x-storefront.section-label number="01">{{ $homeContent->get('why.eyebrow') }}</x-storefront.section-label>

        <h2 class="mt-6 max-w-[880px] text-[42px] leading-[0.96] font-normal sm:text-[72px] lg:text-[82px]">
            {{ $homeContent->get('why.heading') }}<br>
            <span class="text-brand-pink">{{ $homeContent->get('why.subheading') }}</span>
        </h2>

        <div data-animate="stagger" class="mt-14 grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach ($homeContent->get('why.cards', []) as $card)
                <div @class([
                    'min-h-[260px] border p-8 sm:p-10 lg:p-12',
                    'border-brand-black bg-brand-black text-white' => $card['highlighted'] ?? false,
                    'border-brand-black/10 bg-white' => ! ($card['highlighted'] ?? false),
                ])>
                    <span class="text-xs font-bold text-brand-pink">{{ $card['number'] }}</span>
                    <h3 class="mt-24 text-2xl font-normal sm:mt-28">{{ $card['title'] }}</h3>
                    <p class="mt-2 text-sm opacity-80">{{ $card['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
