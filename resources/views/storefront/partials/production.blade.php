<section id="production" class="border-b border-brand-black/10 bg-white py-16 lg:py-24">
    <div class="grid grid-cols-1 items-center gap-10 px-6 lg:grid-cols-2 lg:px-16">
        <div class="aspect-[4/3] w-full overflow-hidden rounded-3xl bg-brand-black/5">
            <img
                src="https://placehold.co/900x675/f7f3ec/1a1512?text=Хлопок"
                alt="{{ $homeContent->get('process.eyebrow') }}"
                class="h-full w-full object-cover"
                loading="lazy"
            >
        </div>

        <div>
            <x-storefront.section-label number="04">{{ $homeContent->get('process.eyebrow') }}</x-storefront.section-label>

            <h2 class="mt-6 text-3xl font-extrabold sm:text-4xl">
                {{ $homeContent->get('process.heading') }}<br>
                <span class="text-brand-pink">{{ $homeContent->get('process.subheading') }}</span>
            </h2>

            <div data-animate="stagger" class="mt-8 flex flex-wrap items-center gap-x-2 gap-y-4 border-t border-brand-black/10 pt-6">
                @foreach ($homeContent->get('process.stages', []) as $i => $stage)
                    @if ($i > 0)
                        <span aria-hidden="true" class="text-brand-black/30">→</span>
                    @endif
                    <div>
                        <p class="text-lg font-extrabold">{{ $stage['title'] }}</p>
                        <p class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">{{ $stage['subtitle'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
