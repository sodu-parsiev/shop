<x-layouts.storefront :title="$homeContent->get('seo.title', config('app.name'))" :home-content="$homeContent" :faqs="$faqs">
    <main>
        @include('storefront.partials.topbar')
        @include('storefront.partials.header')
        @include('storefront.partials.hero')
        @include('storefront.partials.why-us')
        @include('storefront.partials.catalog')
        @include('storefront.partials.for-sellers')
        @include('storefront.partials.production')
        @include('storefront.partials.customization')
        @include('storefront.partials.terms-faq')
        @include('storefront.partials.cta-form')
    </main>

    @include('storefront.partials.order-drawer')
    @include('storefront.partials.footer')
</x-layouts.storefront>
