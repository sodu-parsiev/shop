@props([
    'title' => null,
    'homeContent' => null,
    'faqs' => collect(),
])

@php
    $seo = $homeContent?->get('seo', []) ?? [];
    $pageTitle = $title ?: data_get($seo, 'title', config('app.name'));
    $description = data_get($seo, 'description');
    $keywords = data_get($seo, 'keywords');
    $canonicalUrl = data_get($seo, 'canonical_url') ?: url('/');
    $ogTitle = data_get($seo, 'og_title') ?: $pageTitle;
    $ogDescription = data_get($seo, 'og_description') ?: $description;
    $toAbsoluteUrl = static function (?string $value): ?string {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    };
    $ogImage = $toAbsoluteUrl(data_get($seo, 'og_image') ?: '/brand/model-motion.jpg');
    $icon = $toAbsoluteUrl(data_get($seo, 'icon') ?: '/brand/mark.png');
    $organization = array_filter([
        '@type' => 'Organization',
        '@id' => url('/#organization'),
        'name' => data_get($seo, 'organization_name') ?: config('app.name'),
        'url' => url('/'),
        'email' => $homeContent?->get('cta_section.email'),
        'description' => data_get($seo, 'organization_description') ?: $description,
        'areaServed' => ['@type' => 'Country', 'name' => 'Россия'],
    ]);
    $faqSchema = collect($faqs)
        ->map(fn ($faq): array => [
            '@type' => 'Question',
            'name' => $faq->question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq->answer,
            ],
        ])
        ->values()
        ->all();
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => array_values(array_filter([
            $organization,
            $faqSchema === [] ? null : [
                '@type' => 'FAQPage',
                'mainEntity' => $faqSchema,
            ],
        ])),
    ];
@endphp

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $pageTitle }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
        @if ($keywords)
            <meta name="keywords" content="{{ $keywords }}">
        @endif
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ $toAbsoluteUrl($canonicalUrl) ?? url('/') }}">
        @if ($icon)
            <link rel="icon" href="{{ $icon }}">
            <link rel="apple-touch-icon" href="{{ $icon }}">
        @endif
        <meta property="og:type" content="website">
        <meta property="og:locale" content="ru_RU">
        <meta property="og:title" content="{{ $ogTitle }}">
        @if ($ogDescription)
            <meta property="og:description" content="{{ $ogDescription }}">
        @endif
        @if ($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        @if ($ogDescription)
            <meta name="twitter:description" content="{{ $ogDescription }}">
        @endif
        @if ($ogImage)
            <meta name="twitter:image" content="{{ $ogImage }}">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
    </head>
    <body class="overflow-x-hidden antialiased bg-brand-cream text-brand-black">
        {{ $slot }}
    </body>
</html>
