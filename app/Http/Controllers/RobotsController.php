<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __construct(private readonly RobotsControllerService $service) {}

    public function __invoke(): Response
    {
        return response($this->service->contents(), 200, ['Content-Type' => 'text/plain']);
    }
}
