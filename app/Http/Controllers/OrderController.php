<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderControllerService $service) {}

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $this->service->createFromRequest($request);

        return back()->with('orderSubmitted', true);
    }
}
