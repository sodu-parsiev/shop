<?php

namespace App\Http\Middleware\Services;

use App\Models\Content\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StorefrontRedirectService
{
    public function redirectFor(Request $request): ?RedirectResponse
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return null;
        }

        $canonicalPath = $this->canonicalPath($request->getPathInfo());

        if ($canonicalPath !== $request->getPathInfo()) {
            return redirect()->to($this->targetUrl($canonicalPath, $request), 301);
        }

        if (! Schema::hasTable('redirects')) {
            return null;
        }

        $sourcePath = '/'.ltrim($request->path(), '/');
        $redirect = Redirect::query()
            ->where('source_path', $sourcePath)
            ->where('is_active', true)
            ->first();

        if (! $redirect || ! in_array($redirect->status_code, [301, 302], true)) {
            return null;
        }

        $targetUrl = $this->targetUrl($redirect->target_url, $request);

        if ($this->isLoop($sourcePath, $targetUrl)) {
            return null;
        }

        $redirect->forceFill([
            'hits' => $redirect->hits + 1,
            'last_used_at' => now(),
        ])->save();

        return redirect()->to($targetUrl, $redirect->status_code);
    }

    /**
     * "/catalog/shopper/" and "/catalog/Shopper" both resolve to the same product
     * (MySQL slug lookups ignore case), so send them to the one indexable URL.
     */
    private function canonicalPath(string $path): string
    {
        $canonical = rtrim($path, '/') ?: '/';

        // Only catalog slugs: upload filenames under /storage are uppercase ULIDs.
        return str_starts_with($canonical, '/catalog/') ? strtolower($canonical) : $canonical;
    }

    private function targetUrl(string $targetUrl, Request $request): string
    {
        if (! str_contains($targetUrl, '?') && $request->getQueryString()) {
            return $targetUrl.'?'.$request->getQueryString();
        }

        return $targetUrl;
    }

    private function isLoop(string $sourcePath, string $targetUrl): bool
    {
        $targetPath = parse_url($targetUrl, PHP_URL_PATH) ?: '/';

        return rtrim($sourcePath, '/') === rtrim($targetPath, '/');
    }
}
