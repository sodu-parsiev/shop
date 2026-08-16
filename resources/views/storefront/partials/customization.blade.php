<section id="customization" class="border-b border-brand-black/10 bg-brand-cream py-16 lg:py-24">
    <div class="px-6 lg:px-16">
        <x-storefront.section-label number="05">{{ $homeContent->get('customization_section.eyebrow') }}</x-storefront.section-label>

        <h2 class="mt-6 max-w-2xl text-3xl font-extrabold sm:text-4xl">
            {{ $homeContent->get('customization_section.heading') }}<br>
            <span class="text-brand-pink">{{ $homeContent->get('customization_section.heading_accent') }}</span>
        </h2>

        <div data-animate="stagger" class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2">
            @foreach ($customizationServices as $i => $service)
                <div @class([
                    'rounded-2xl p-6',
                    'sm:col-span-2 bg-brand-black text-white' => $i === 0,
                    'border border-brand-black/10 bg-white' => $i !== 0,
                ])>
                    <span @class([
                        'text-xs font-bold',
                        'opacity-60' => $i === 0,
                        'text-brand-pink' => $i !== 0,
                    ])>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="mt-3 text-lg font-extrabold">{{ $service->name }}</h3>
                    <p @class(['mt-2 text-sm', 'opacity-80' => $i === 0, 'text-brand-black/60' => $i !== 0])>{{ $service->description }}</p>

                    <a href="#apply" @class([
                        'mt-4 inline-flex items-center gap-2 border-t pt-3 text-xs font-bold tracking-wide uppercase',
                        'border-white/10' => $i === 0,
                        'border-brand-black/10' => $i !== 0,
                    ])>
                        {{ $homeContent->get('customization_section.cta') }}
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
