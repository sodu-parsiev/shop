<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderControllerService $service) {}

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->service->createFromRequest($request);

        $request->session()->forget('order_submission_token');

        return back()
            ->with('orderSubmitted', true)
            ->with('orderRequestNumber', $order->request_number);
    }
}
