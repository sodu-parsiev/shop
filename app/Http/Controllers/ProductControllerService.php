<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use App\Models\Content\HomePageContent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductControllerService
{
    /**
     * @return array<string, mixed>
     */
    public function getProductPageData(Product $product): array
    {
        abort_unless($product->status === ProductStatus::Active && $product->show_on_landing, 404);

        $product->load(['category', 'colors', 'densities', 'sizes', 'images', 'customizationServices', 'priceTiers']);

        $homeContent = HomePageContent::query()->firstOrCreate(['id' => 1], ['content' => []]);
        $description = $this->descriptionFor($product);

        return [
            'product' => $product,
            'homeContent' => $homeContent,
            'relatedProducts' => $this->relatedProducts($product),
            'seoMeta' => [
                'title' => $product->meta_title ?: $product->name.' — '.config('app.name'),
                'description' => $product->meta_description ?: $description,
                'canonical_url' => $product->canonical_url ?: $product->publicUrl(),
                'og_title' => $product->meta_title ?: $product->name,
                'og_description' => $product->meta_description ?: $description,
                'og_image' => $product->og_image ?: $product->cover_image,
                'og_type' => 'product',
            ],
            'schemaGraph' => [
                $this->breadcrumbSchema($product),
                $this->productSchema($product, $description),
            ],
        ];
    }

    private function descriptionFor(Product $product): string
    {
        return Str::limit(strip_tags($product->short_description ?: $product->description ?: $product->name), 180);
    }

    /**
     * @return Collection<int, Product>
     */
    private function relatedProducts(Product $product): Collection
    {
        return Product::query()
            ->with(['category', 'colors', 'densities', 'sizes', 'images'])
            ->where('status', ProductStatus::Active)
            ->where('show_on_landing', true)
            ->where('id', '!=', $product->getKey())
            ->orderByDesc('featured')
            ->orderBy('sort_order')
            ->limit(3)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function breadcrumbSchema(Product $product): array
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
                    'name' => 'Каталог',
                    'item' => url('/#catalog'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $product->name,
                    'item' => $product->publicUrl(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productSchema(Product $product, string $description): array
    {
        return array_filter([
            '@type' => 'Product',
            '@id' => $product->publicUrl().'#product',
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => $description,
            'category' => $product->category?->name,
            'image' => $this->productImages($product),
            'brand' => [
                '@type' => 'Brand',
                'name' => config('app.name'),
            ],
            'additionalProperty' => $this->productProperties($product),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function productImages(Product $product): array
    {
        $images = $product->images
            ->pluck('path')
            ->prepend($product->cover_image)
            ->filter()
            ->unique()
            ->map(fn (string $path): string => $this->absoluteUrl($path))
            ->values()
            ->all();

        return $images === [] ? [asset('brand/catalog-white-v2.jpg')] : $images;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function productProperties(Product $product): array
    {
        return collect([
            ['name' => 'Состав', 'value' => $product->composition],
            ['name' => 'Посадка', 'value' => $product->fit],
            ['name' => 'Плотности', 'value' => $product->densities->pluck('name')->implode(', ')],
            ['name' => 'Размеры', 'value' => $product->sizes->pluck('name')->implode(', ')],
            ['name' => 'Цвета', 'value' => $product->colors->pluck('name')->implode(', ')],
            ['name' => 'Минимальная партия', 'value' => number_format($product->moq, 0, ',', ' ').' шт.'],
            ['name' => 'Цена чистого текстиля', 'value' => $product->startingPriceLabel()],
        ])
            ->filter(fn (array $property): bool => filled($property['value']))
            ->map(fn (array $property): array => [
                '@type' => 'PropertyValue',
                'name' => $property['name'],
                'value' => $property['value'],
            ])
            ->values()
            ->all();
    }

    private function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
