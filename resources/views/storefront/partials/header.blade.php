@php
    $contactHref = request()->routeIs('home', 'products.show') ? '#contacts' : route('home').'#contacts';
@endphp

<header class="sticky top-0 z-40 border-b border-brand-black/10 bg-white/95 backdrop-blur">
    <div x-data="mobileNav" class="relative storefront-shell flex h-[72px] items-center justify-between gap-4 sm:h-[86px]">
        <a href="{{ route('home') }}" class="flex items-center" aria-label="{{ config('app.name') }} — на главную">
            <img src="{{ asset('brand/logo-dark.png') }}" alt="{{ config('app.name') }}" class="h-11 w-auto sm:h-14">
        </a>

        <nav class="hidden items-center gap-8 text-sm font-bold md:flex">
            <a href="{{ route('home') }}#catalog" class="hover:text-brand-pink">{{ $homeContent->get('nav.catalog') }}</a>
            <a href="{{ route('home') }}#production" class="hover:text-brand-pink">{{ $homeContent->get('nav.production') }}</a>
            <a href="{{ route('home') }}#customization" class="hover:text-brand-pink">{{ $homeContent->get('nav.customization') }}</a>
            <a href="{{ route('home') }}#terms" class="hover:text-brand-pink">{{ $homeContent->get('nav.terms') }}</a>
            <a href="{{ $contactHref }}" class="hover:text-brand-pink">{{ $homeContent->get('nav.contacts') }}</a>
        </nav>

        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="open = !open"
                class="text-sm font-bold md:hidden"
                aria-controls="mobile-nav"
                :aria-expanded="open.toString()"
            >
                Меню
            </button>

            <button type="button" @click="$store.orderBuilder.open()" class="inline-flex items-center gap-2 bg-brand-pink px-4 py-3 text-sm font-bold text-white sm:px-5">
                {{ $homeContent->get('nav.apply_button') }}
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-xs" x-text="$store.orderBuilder.lines.length">0</span>
            </button>
        </div>

        <div
            id="mobile-nav"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.outside="open = false"
            class="absolute inset-x-0 top-full z-40 flex flex-col gap-4 border-b border-brand-black/10 bg-white px-5 py-5 text-sm font-bold md:hidden"
        >
            <a href="{{ route('home') }}#catalog" @click="open = false">{{ $homeContent->get('nav.catalog') }}</a>
            <a href="{{ route('home') }}#production" @click="open = false">{{ $homeContent->get('nav.production') }}</a>
            <a href="{{ route('home') }}#customization" @click="open = false">{{ $homeContent->get('nav.customization') }}</a>
            <a href="{{ route('home') }}#terms" @click="open = false">{{ $homeContent->get('nav.terms') }}</a>
            <a href="{{ $contactHref }}" @click="open = false">{{ $homeContent->get('nav.contacts') }}</a>
        </div>
    </div>
</header>
