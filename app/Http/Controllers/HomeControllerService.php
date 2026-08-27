<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\Size;
use App\Models\Content\Faq;
use App\Models\Content\HomePageContent;
use Illuminate\Support\Collection;

class HomeControllerService
{
    /**
     * @return array{
     *     products: Collection<int, Product>,
     *     categories: Collection<int, Category>,
     *     colors: Collection<int, Color>,
     *     densities: Collection<int, Density>,
     *     sizes: Collection<int, Size>,
     *     customizationServices: Collection<int, CustomizationService>,
     *     faqs: Collection<int, Faq>,
     *     homeContent: HomePageContent,
     * }
     */
    public function getHomePageData(): array
    {
        $products = Product::query()
            ->with(['category', 'colors', 'densities', 'sizes', 'images', 'priceTiers'])
            ->where('show_on_landing', true)
            ->where('status', ProductStatus::Active)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->get();

        $productIds = $products->pluck('id');

        $categories = Category::query()
            ->where('is_active', true)
            ->whereIn('id', $products->pluck('category_id')->filter()->unique())
            ->orderBy('sort_order')
            ->get();

        $colors = Color::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->whereIn('products.id', $productIds))
            ->orderBy('sort_order')
            ->get();

        $densities = Density::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->whereIn('products.id', $productIds))
            ->orderBy('gsm')
            ->get();

        $sizes = Size::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->whereIn('products.id', $productIds))
            ->orderBy('sort_order')
            ->get();

        $customizationServices = CustomizationService::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $homeContent = HomePageContent::query()->firstOrCreate(['id' => 1], ['content' => []]);

        return [
            'products' => $products,
            'categories' => $categories,
            'colors' => $colors,
            'densities' => $densities,
            'sizes' => $sizes,
            'customizationServices' => $customizationServices,
            'faqs' => $faqs,
            'homeContent' => $homeContent,
        ];
    }
}
