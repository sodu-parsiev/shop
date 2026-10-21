<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use App\Models\Content\Faq;
use App\Models\Content\HomePageContent;
use App\Models\Content\Page;
use Illuminate\Support\Carbon;
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
                // Trailing slash matches the homepage's canonical URL.
                'loc' => url('/').'/',
                'lastmod' => $this->homeLastModified(),
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
            ->whereIn('slug', ['privacy', 'consent', 'requisites', 'size-guide'])
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(fn (Page $page) => $urls->push([
                'loc' => url('/'.$page->slug),
                'lastmod' => $page->updated_at?->toAtomString(),
                'priority' => '0.5',
            ]));

        return $urls;
    }

    /**
     * When the homepage's content last changed — a lastmod that is always "now" teaches crawlers to ignore it.
     */
    private function homeLastModified(): ?string
    {
        $latest = collect([
            HomePageContent::query()->max('updated_at'),
            Product::query()->max('updated_at'),
            Faq::query()->max('updated_at'),
        ])->filter()->max();

        return $latest ? Carbon::parse($latest)->toAtomString() : null;
    }
}
