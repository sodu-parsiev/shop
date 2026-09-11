<section class="border-b border-brand-black/10 bg-brand-cream py-16 lg:py-28">
    <div class="storefront-shell">
        <x-storefront.section-label number="01">{{ $homeContent->get('why.eyebrow') }}</x-storefront.section-label>

        <h2 class="mt-6 max-w-[1100px] text-[28px] leading-[1.12] font-normal sm:text-[38px] lg:text-[48px]">
            {{ $homeContent->get('why.heading') }}
        </h2>

        <p class="mt-5 max-w-2xl text-base text-brand-black/60 sm:text-lg">
            {{ $homeContent->get('why.intro') }}
        </p>

        @php
            $whyItems = collect($homeContent->get('why.cards', []));
            $whyColumnSize = max((int) ceil($whyItems->count() / 2), 1);
            $whyColumns = $whyItems->chunk($whyColumnSize);
        @endphp

        <div class="mt-14 grid grid-cols-1 gap-x-16 gap-y-10 sm:grid-cols-2">
            @foreach ($whyColumns as $column)
                <div data-animate="stagger" class="flex flex-col gap-10">
                    @foreach ($column as $card)
                        <div>
                            <h3 @class([
                                'text-xl font-bold sm:text-2xl',
                                'text-brand-pink' => $loop->index % 2 === 0,
                                'text-brand-black' => $loop->index % 2 !== 0,
                            ])>{{ $card['title'] }}</h3>
                            <p class="mt-2 text-sm text-brand-black/60">{{ $card['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
