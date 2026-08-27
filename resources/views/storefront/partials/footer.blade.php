<footer class="bg-brand-black py-12 text-white/70 lg:py-16">
    <div class="storefront-shell">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <img src="{{ asset('brand/logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto">
                <p class="mt-5 max-w-xs text-sm leading-relaxed">{{ $homeContent->get('footer.tagline') }}</p>
            </div>

            <div>
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">{{ $homeContent->get('footer.nav_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('home') }}#catalog" class="hover:text-white">{{ $homeContent->get('nav.catalog') }}</a></li>
                    <li><a href="{{ route('home') }}#production" class="hover:text-white">{{ $homeContent->get('nav.production') }}</a></li>
                    <li><a href="{{ route('home') }}#customization" class="hover:text-white">{{ $homeContent->get('nav.customization') }}</a></li>
                    <li><a href="{{ route('home') }}#terms" class="hover:text-white">{{ $homeContent->get('nav.terms') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">{{ $homeContent->get('footer.legal_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-white">{{ $homeContent->get('footer.privacy') }}</a></li>
                    <li><a href="{{ route('legal.consent') }}" class="hover:text-white">{{ $homeContent->get('footer.consent') }}</a></li>
                    <li><a href="{{ route('legal.requisites') }}" class="hover:text-white">{{ $homeContent->get('footer.requisites') }}</a></li>
                    <li><a href="{{ route('legal.size-guide') }}" class="hover:text-white">{{ $homeContent->get('footer.size_guide') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">{{ $homeContent->get('footer.contacts_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li>
                        <a href="mailto:{{ $homeContent->get('cta_section.email') }}" class="hover:text-white" @click="storefrontAnalytics.track('contact_click', { type: 'email', location: 'footer' })">
                            {{ $homeContent->get('cta_section.email') }}
                        </a>
                    </li>
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
