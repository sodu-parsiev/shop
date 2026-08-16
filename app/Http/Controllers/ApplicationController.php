<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use Illuminate\Http\RedirectResponse;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationControllerService $service) {}

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $this->service->createFromRequest($request);

        return back()->with('applicationSubmitted', true);
    }
}
