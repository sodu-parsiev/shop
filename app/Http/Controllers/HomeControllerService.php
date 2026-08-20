<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Product;
use App\Models\Content\Faq;
use App\Models\Content\HomePageContent;
use Illuminate\Support\Collection;

class HomeControllerService
{
    /**
     * @return array{
     *     products: Collection<int, Product>,
     *     categories: Collection<int, Category>,
     *     customizationServices: Collection<int, CustomizationService>,
     *     faqs: Collection<int, Faq>,
     *     homeContent: HomePageContent,
     * }
     */
    public function getHomePageData(): array
    {
        $products = Product::query()
            ->with(['category', 'colors', 'densities', 'sizes', 'images'])
            ->where('show_on_landing', true)
            ->where('status', ProductStatus::Active)
            ->orderBy('sort_order')
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
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
            'customizationServices' => $customizationServices,
            'faqs' => $faqs,
            'homeContent' => $homeContent,
        ];
    }
}
