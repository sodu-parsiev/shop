<x-layouts.storefront
    title="Страница не найдена — {{ config('app.name') }}"
    :home-content="$homeContent"
    :seo-meta="['canonical_url' => url()->current()]"
    robots="noindex, follow"
>
    <main>
        @include('storefront.partials.topbar')
        @include('storefront.partials.header')

        <section class="bg-white py-16 text-brand-black lg:py-28">
            <div class="storefront-shell max-w-3xl">
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">Ошибка 404</p>
                <h1 class="mt-4 text-[42px] leading-[0.98] font-normal sm:text-[64px] lg:text-[78px]">Такой страницы нет</h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-brand-black/65">
                    Возможно, ссылка устарела или в адресе опечатка. Посмотрите каталог или оставьте заявку — менеджер подберёт модели и пришлёт прайс.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('home') }}#catalog" class="inline-flex items-center justify-between gap-8 bg-brand-pink px-6 py-4 text-sm font-bold text-white">
                        Перейти в каталог
                        <span aria-hidden="true">&#8599;&#65038;</span>
                    </a>
                    <a href="{{ route('home') }}#contacts" class="inline-flex items-center justify-center border border-brand-black px-6 py-4 text-sm font-bold">
                        Оставить заявку
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-4 text-sm font-bold underline">
                        На главную
                    </a>
                </div>
            </div>
        </section>
    </main>

    @include('storefront.partials.order-drawer')
    @include('storefront.partials.footer')
</x-layouts.storefront>
