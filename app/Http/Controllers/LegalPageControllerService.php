<?php

namespace App\Http\Controllers;

use App\Models\Content\HomePageContent;
use App\Models\Content\Page;
use Illuminate\Support\Str;

class LegalPageControllerService
{
    /**
     * @return array<string, mixed>
     */
    public function getLegalPageData(string $slug): array
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $homeContent = HomePageContent::query()->firstOrCreate(['id' => 1], ['content' => []]);
        $description = $page->meta_description ?: Str::limit(strip_tags($page->body ?: $page->title ?: $page->name), 180);

        return [
            'page' => $page,
            'homeContent' => $homeContent,
            'seoMeta' => [
                'title' => $page->meta_title ?: $page->title ?: $page->name,
                'description' => $description,
                'canonical_url' => $page->canonical_url ?: url('/'.$page->slug),
                'og_title' => $page->meta_title ?: $page->title ?: $page->name,
                'og_description' => $description,
                'og_image' => $page->og_image,
                'og_type' => 'article',
            ],
            'schemaGraph' => [
                $this->breadcrumbSchema($page),
                [
                    '@type' => 'WebPage',
                    '@id' => url('/'.$page->slug).'#webpage',
                    'name' => $page->title ?: $page->name,
                    'url' => url('/'.$page->slug),
                    'description' => $description,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function breadcrumbSchema(Page $page): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Главная',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $page->title ?: $page->name,
                    'item' => url('/'.$page->slug),
                ],
            ],
        ];
    }
}
