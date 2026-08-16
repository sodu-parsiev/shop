<section id="terms" class="border-b border-brand-black/10">
    <div class="bg-brand-black py-16 text-white lg:py-24">
        <div class="px-6 lg:px-16">
            <x-storefront.section-label number="06">{{ $homeContent->get('terms.eyebrow') }}</x-storefront.section-label>

            <h2 class="mt-6 max-w-2xl text-3xl font-extrabold sm:text-4xl">
                {{ $homeContent->get('terms.heading_main') }}
                <span class="text-brand-pink">{{ $homeContent->get('terms.heading_accent') }}</span>
            </h2>

            <div data-animate="stagger" class="mt-10 grid grid-cols-1 gap-6 divide-y divide-white/10 border-t border-white/10 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">
                @foreach ($homeContent->get('terms.stats', []) as $stat)
                    <div class="pt-6 sm:px-6 sm:pt-0 sm:first:pl-0">
                        <p class="text-2xl font-extrabold text-brand-pink">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs font-bold tracking-wide uppercase">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-sm text-white/60">{{ $stat['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-brand-cream py-16 lg:py-24">
        <div class="grid grid-cols-1 gap-10 px-6 lg:grid-cols-2 lg:px-16">
            <div x-data="faqAccordion">
                <p class="text-xs font-bold tracking-widest text-brand-black/40 uppercase">{{ $homeContent->get('faq.heading') }}</p>
                <h3 class="mt-2 text-2xl font-extrabold">
                    {{ $homeContent->get('faq.subheading_main') }}
                    <span class="text-brand-pink">{{ $homeContent->get('faq.subheading_accent') }}</span>
                </h3>

                <div class="mt-6 divide-y divide-brand-black/10 border-y border-brand-black/10">
                    @foreach ($faqs as $i => $faq)
                        <div>
                            <button
                                type="button"
                                @click="toggle({{ $i }})"
                                class="flex w-full items-center justify-between gap-4 py-4 text-left text-sm font-bold"
                            >
                                {{ $faq->question }}
                                <span aria-hidden="true" class="text-brand-pink" x-text="openIndex === {{ $i }} ? '−' : '+'"></span>
                            </button>
                            <div
                                x-show="openIndex === {{ $i }}"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                            >
                                <p class="pb-4 text-sm text-brand-black/70">{{ $faq->answer }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-brand-black/10 bg-white p-8">
                <p class="text-xs font-bold tracking-widest text-brand-black/40 uppercase">{{ $homeContent->get('portal_callout.eyebrow') }}</p>
                <h3 class="mt-2 text-2xl font-extrabold">{{ $homeContent->get('portal_callout.heading') }}</h3>
                <p class="mt-3 text-sm text-brand-black/70">{{ $homeContent->get('portal_callout.description') }}</p>
                <a href="#apply" class="mt-6 inline-flex items-center gap-2 bg-brand-black px-6 py-3 text-sm font-bold text-white">
                    {{ $homeContent->get('portal_callout.cta') }}
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>
    </div>
</section>
