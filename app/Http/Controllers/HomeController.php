<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly HomeControllerService $service) {}

    public function __invoke(): View
    {
        return view('storefront.home', $this->service->getHomePageData());
    }
}
