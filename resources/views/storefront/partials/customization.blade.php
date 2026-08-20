<section id="customization" class="border-b border-brand-black/10 bg-brand-cream py-16 lg:py-28">
    <div class="mx-auto max-w-[1324px] px-5 sm:px-8 lg:px-0">
        <x-storefront.section-label number="05">{{ $homeContent->get('customization_section.eyebrow') }}</x-storefront.section-label>

        <h2 class="mt-6 max-w-[1120px] text-[42px] leading-[0.98] font-normal sm:text-[64px] lg:text-[76px]">
            {{ $homeContent->get('customization_section.heading') }}<br>
            <span class="text-brand-pink">{{ $homeContent->get('customization_section.heading_accent') }}</span>
        </h2>

        <div data-animate="stagger" class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach ($customizationServices as $i => $service)
                <div @class([
                    'min-h-[220px] border p-8',
                    'sm:col-span-2 border-brand-black bg-brand-black text-white' => $i === 0,
                    'border border-brand-black/10 bg-white' => $i !== 0,
                ])>
                    @if ($i === 0)
                        <div class="grid gap-8 sm:grid-cols-[1fr_0.95fr] sm:items-center">
                            <div>
                                <span class="text-xs font-bold opacity-70">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3 class="mt-24 text-2xl font-normal">{{ $service->name }}</h3>
                                <p class="mt-3 text-sm opacity-80">{{ $service->description }}</p>
                            </div>
                            <div class="flex min-h-[220px] -rotate-2 items-center justify-center bg-brand-pink p-8 text-center text-5xl font-black leading-none text-white sm:min-h-[260px] sm:text-6xl">
                                СВОЙ<br>ХОД
                            </div>
                        </div>
                    @else
                        <span class="text-xs font-bold text-brand-pink">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-20 text-2xl font-normal">{{ $service->name }}</h3>
                        <p class="mt-3 text-sm text-brand-black/60">{{ $service->description }}</p>

                        <a href="#contacts" class="mt-8 inline-flex items-center gap-2 border-t border-brand-black/10 pt-4 text-xs font-bold tracking-wide uppercase">
                            {{ $homeContent->get('customization_section.cta') }}
                            <span aria-hidden="true">&#8599;&#65038;</span>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
