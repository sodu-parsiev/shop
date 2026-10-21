<?php

namespace App\Http\Controllers;

class RobotsControllerService
{
    /**
     * Tracking parameters Yandex should treat as the same page (ysclid is added by Yandex search itself).
     */
    private const TRACKING_PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid', 'ysclid', 'gclid', 'fbclid'];

    public function contents(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /livewire',
            'Disallow: /up',
            'Clean-param: '.implode('&', self::TRACKING_PARAMS),
            'Sitemap: '.route('sitemap'),
            '',
        ]);
    }
}
