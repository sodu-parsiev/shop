<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapControllerService $service) {}

    public function __invoke(): Response
    {
        return response()
            ->view('storefront.sitemap', ['urls' => $this->service->urls()])
            ->header('Content-Type', 'application/xml');
    }
}
