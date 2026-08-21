<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function __construct(private readonly LegalPageControllerService $service) {}

    public function __invoke(Request $request): View
    {
        return view('storefront.legal', $this->service->getLegalPageData((string) $request->route('slug')));
    }
}
