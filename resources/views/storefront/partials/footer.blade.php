<footer class="bg-brand-black py-12 text-white/70 lg:py-16">
    <div class="mx-auto max-w-[1324px] px-5 sm:px-8 lg:px-0">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <img src="{{ asset('brand/logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto">
                <p class="mt-5 max-w-xs text-sm leading-relaxed">{{ $homeContent->get('footer.tagline') }}</p>
            </div>

            <div>
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">{{ $homeContent->get('footer.nav_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="#catalog" class="hover:text-white">{{ $homeContent->get('nav.catalog') }}</a></li>
                    <li><a href="#production" class="hover:text-white">{{ $homeContent->get('nav.production') }}</a></li>
                    <li><a href="#customization" class="hover:text-white">{{ $homeContent->get('nav.customization') }}</a></li>
                    <li><a href="#terms" class="hover:text-white">{{ $homeContent->get('nav.terms') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">{{ $homeContent->get('footer.order_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($homeContent->get('footer.order_items', []) as $item)
                        <li>{{ $item['label'] }}</li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">{{ $homeContent->get('footer.contacts_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li>{{ $homeContent->get('cta_section.email') }}</li>
                    <li>{{ $homeContent->get('cta_section.address') }}</li>
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs sm:flex-row sm:justify-between">
            <span>{{ $homeContent->get('footer.copyright') }}</span>
            <span>{{ $homeContent->get('footer.made_for') }}</span>
        </div>
    </div>
</footer>
