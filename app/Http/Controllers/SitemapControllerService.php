<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use App\Models\Content\Page;
use Illuminate\Support\Collection;

class SitemapControllerService
{
    /**
     * @return Collection<int, array{loc: string, lastmod: ?string, priority: string}>
     */
    public function urls(): Collection
    {
        $urls = collect([
            [
                'loc' => url('/'),
                'lastmod' => now()->toAtomString(),
                'priority' => '1.0',
            ],
        ]);

        Product::query()
            ->where('status', ProductStatus::Active)
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(fn (Product $product) => $urls->push([
                'loc' => $product->publicUrl(),
                'lastmod' => $product->updated_at?->toAtomString(),
                'priority' => '0.8',
            ]));

        Page::query()
            ->where('is_published', true)
            ->whereIn('slug', ['privacy', 'consent', 'requisites'])
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(fn (Page $page) => $urls->push([
                'loc' => url('/'.$page->slug),
                'lastmod' => $page->updated_at?->toAtomString(),
                'priority' => '0.5',
            ]));

        return $urls;
    }
}
