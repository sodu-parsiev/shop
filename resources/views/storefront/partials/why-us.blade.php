<section class="border-b border-brand-black/10 bg-brand-cream py-16 lg:py-24">
    <div class="px-6 lg:px-16">
        <x-storefront.section-label number="01">{{ $homeContent->get('why.eyebrow') }}</x-storefront.section-label>

        <h2 class="mt-6 max-w-2xl text-3xl font-extrabold sm:text-4xl">
            {{ $homeContent->get('why.heading') }}<br>
            <span class="text-brand-pink">{{ $homeContent->get('why.subheading') }}</span>
        </h2>

        <div data-animate="stagger" class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ($homeContent->get('why.cards', []) as $card)
                <div @class([
                    'rounded-2xl border p-6',
                    'border-brand-black bg-brand-black text-white' => $card['highlighted'] ?? false,
                    'border-brand-black/10 bg-white' => ! ($card['highlighted'] ?? false),
                ])>
                    <span class="text-xs font-bold tracking-widest opacity-60">{{ $card['number'] }}</span>
                    <h3 class="mt-3 text-lg font-extrabold">{{ $card['title'] }}</h3>
                    <p class="mt-2 text-sm opacity-80">{{ $card['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
