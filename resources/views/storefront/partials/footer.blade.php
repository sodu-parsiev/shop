<footer id="contacts" class="bg-brand-black py-12 text-white/70">
    <div class="px-6 lg:px-16">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-12 w-auto">
                <p class="mt-3 text-sm">{{ $homeContent->get('footer.tagline') }}</p>
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
