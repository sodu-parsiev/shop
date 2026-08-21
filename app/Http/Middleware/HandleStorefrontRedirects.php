<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Services\StorefrontRedirectService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleStorefrontRedirects
{
    public function __construct(private readonly StorefrontRedirectService $service) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $redirect = $this->service->redirectFor($request);

        if ($redirect) {
            return $redirect;
        }

        return $next($request);
    }
}
