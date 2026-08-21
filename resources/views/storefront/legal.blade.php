<x-layouts.storefront
    :title="$seoMeta['title']"
    :home-content="$homeContent"
    :seo-meta="$seoMeta"
    :schema-graph="$schemaGraph"
>
    <main>
        @include('storefront.partials.topbar')
        @include('storefront.partials.header')

        <section class="bg-white py-12 text-brand-black lg:py-20">
            <div class="storefront-shell">
                <nav class="flex flex-wrap gap-2 text-xs font-bold tracking-wide text-brand-black/45 uppercase">
                    <a href="{{ route('home') }}" class="hover:text-brand-pink">Главная</a>
                    <span>/</span>
                    <span>{{ $page->title ?: $page->name }}</span>
                </nav>

                <div class="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-[0.34fr_1fr]">
                    <div>
                        <x-storefront.section-label number="Документ">B2B</x-storefront.section-label>
                    </div>
                    <article class="max-w-4xl">
                        <h1 class="text-[42px] leading-[0.98] font-normal sm:text-[64px] lg:text-[82px]">
                            {{ $page->title ?: $page->name }}
                        </h1>

                        <div class="mt-8 space-y-5 text-base leading-relaxed text-brand-black/70">
                            @foreach (preg_split('/\R{2,}/', trim((string) $page->body)) ?: [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </main>

    @include('storefront.partials.order-drawer')
    @include('storefront.partials.footer')
</x-layouts.storefront>
