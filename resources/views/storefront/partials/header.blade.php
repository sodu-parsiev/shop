<header class="sticky top-0 z-40 border-b border-brand-black/10 bg-brand-cream/95 backdrop-blur">
    <div x-data="mobileNav" class="flex items-center justify-between px-6 py-4 lg:px-16">
        <a href="{{ route('home') }}" class="flex items-center" aria-label="{{ config('app.name') }} — на главную">
            <img src="{{ asset('images/logo-dark.png') }}" alt="{{ config('app.name') }}" class="h-12 w-auto">
        </a>

        <nav class="hidden items-center gap-8 text-sm font-bold uppercase md:flex">
            <a href="#catalog" class="hover:text-brand-pink">{{ $homeContent->get('nav.catalog') }}</a>
            <a href="#production" class="hover:text-brand-pink">{{ $homeContent->get('nav.production') }}</a>
            <a href="#customization" class="hover:text-brand-pink">{{ $homeContent->get('nav.customization') }}</a>
            <a href="#terms" class="hover:text-brand-pink">{{ $homeContent->get('nav.terms') }}</a>
            <a href="#contacts" class="hover:text-brand-pink">{{ $homeContent->get('nav.contacts') }}</a>
        </nav>

        <div class="flex items-center gap-3">
            <a href="#apply" class="hidden items-center gap-2 bg-brand-pink px-5 py-2 text-sm font-bold text-white sm:inline-flex">
                {{ $homeContent->get('nav.apply_button') }}
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-xs">0</span>
            </a>

            <button
                type="button"
                @click="open = !open"
                class="rounded-full border border-brand-black/20 p-2 md:hidden"
                aria-label="Открыть меню"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.outside="open = false"
            class="absolute inset-x-0 top-full z-40 flex flex-col gap-4 border-b border-brand-black/10 bg-brand-cream px-4 py-4 text-sm font-bold uppercase md:hidden"
        >
            <a href="#catalog" @click="open = false">{{ $homeContent->get('nav.catalog') }}</a>
            <a href="#production" @click="open = false">{{ $homeContent->get('nav.production') }}</a>
            <a href="#customization" @click="open = false">{{ $homeContent->get('nav.customization') }}</a>
            <a href="#terms" @click="open = false">{{ $homeContent->get('nav.terms') }}</a>
            <a href="#contacts" @click="open = false">{{ $homeContent->get('nav.contacts') }}</a>
            <a href="#apply" @click="open = false" class="text-brand-pink">{{ $homeContent->get('nav.apply_button') }}</a>
        </div>
    </div>
</header>
