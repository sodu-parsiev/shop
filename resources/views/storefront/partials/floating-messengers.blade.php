@php
    $messengers = collect([
        'whatsapp' => [
            'href' => 'https://wa.me/' . preg_replace('/\D+/', '', (string) $homeContent->get('messengers.whatsapp.value')),
            'label' => 'Написать в WhatsApp',
        ],
        'telegram' => [
            'href' => 'https://t.me/' . ltrim((string) $homeContent->get('messengers.telegram.value'), '@'),
            'label' => 'Написать в Telegram',
        ],
        'max' => [
            'href' => 'https://max.ru/u/' . ltrim((string) $homeContent->get('messengers.max.value'), '@'),
            'label' => 'Написать в MAX',
        ],
    ])->filter(fn ($channel, $key) =>
        $homeContent->get("messengers.{$key}.enabled")
        && filled($homeContent->get("messengers.{$key}.value"))
    );
@endphp

@if ($messengers->isNotEmpty())
    <div class="fixed right-4 bottom-4 z-40 flex flex-col gap-3 sm:right-6 sm:bottom-6" style="padding-bottom: env(safe-area-inset-bottom);">
        @foreach ($messengers as $key => $channel)
            <a
                href="{{ $channel['href'] }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="{{ $channel['label'] }}"
                class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-black text-white shadow-lg transition hover:bg-brand-pink sm:h-14 sm:w-14"
                @click="storefrontAnalytics.track('contact_click', { type: '{{ $key }}', location: 'floating' })"
            >
                @include('storefront.partials.icons.' . $key)
            </a>
        @endforeach
    </div>
@endif
