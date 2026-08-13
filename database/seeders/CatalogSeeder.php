<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Size;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['T-Shirts', 'Hoodies', 'Tote Bags', 'Stickers'])
            ->map(fn (string $name, int $i) => Category::factory()->create([
                'name' => $name,
                'sort_order' => $i,
            ]));

        $colors = collect([
            'Black' => '#000000',
            'White' => '#FFFFFF',
            'Navy' => '#1B1F3B',
            'Red' => '#C8102E',
        ])->map(fn (string $hex, string $name) => Color::factory()->create([
            'name' => $name,
            'hex_code' => $hex,
        ]))->values();

        $sizes = collect(['S', 'M', 'L', 'XL'])
            ->map(fn (string $name, int $i) => Size::factory()->create([
                'name' => $name,
                'sort_order' => $i,
            ]));

        $densities = collect([180, 220, 280, 350])
            ->map(fn (int $gsm, int $i) => Density::factory()->create([
                'name' => "{$gsm} gsm",
                'gsm' => $gsm,
                'sort_order' => $i,
            ]));

        $services = collect(['Embroidery', 'Screen Print', 'DTG Print', 'Vinyl Cut'])
            ->map(fn (string $name, int $i) => CustomizationService::factory()->create([
                'name' => $name,
                'sort_order' => $i,
            ]));

        $classicTee = Product::factory()->create([
            'name' => 'Classic Tee',
            'category_id' => $categories->first()->id,
            'slug' => 'classic-tee',
            'sku' => 'SKU-CLASSIC-TEE',
            'short_description' => 'A timeless, everyday crew neck t-shirt.',
            'description' => 'Our Classic Tee is made from soft, durable cotton and cut for a comfortable regular fit. Perfect as a blank canvas for custom prints.',
            'composition' => '100% combed cotton',
            'fit' => 'Regular Fit',
            'moq' => 25,
            'stock_conditions' => 'Ships within 3-5 business days once printed.',
            'status' => ProductStatus::Active,
            'recommended' => true,
            'sort_order' => 0,
            'meta_title' => 'Classic Tee | Custom Printed T-Shirts',
            'meta_description' => 'Order the Classic Tee in bulk with custom printing.',
        ]);
        $classicTee->customizationServices()->attach($services->take(2)->pluck('id'));
        ProductVariant::factory()->create([
            'product_id' => $classicTee->id,
            'color_id' => $colors->first()->id,
            'size_id' => $sizes->first()->id,
            'density_id' => $densities->first()->id,
            'availability_status' => AvailabilityStatus::InStock,
            'stock_quantity' => 250,
        ]);

        $toteBag = Product::factory()->create([
            'name' => 'Canvas Tote Bag',
            'category_id' => $categories->get(2)->id,
            'slug' => 'canvas-tote-bag',
            'sku' => 'SKU-CANVAS-TOTE',
            'short_description' => 'A sturdy canvas tote bag for everyday carry.',
            'description' => 'Heavyweight canvas tote bag with reinforced handles, ideal for embroidery or vinyl branding.',
            'composition' => '100% cotton canvas',
            'fit' => null,
            'moq' => 50,
            'stock_conditions' => 'Made to order; allow 7-10 business days.',
            'status' => ProductStatus::Active,
            'recommended' => false,
            'sort_order' => 1,
            'meta_title' => 'Canvas Tote Bag | Custom Branded Totes',
            'meta_description' => 'Order custom branded canvas tote bags in bulk.',
        ]);
        $toteBag->customizationServices()->attach($services->last()->id);
        ProductVariant::factory()->create([
            'product_id' => $toteBag->id,
            'color_id' => $colors->get(1)->id,
            'size_id' => $sizes->get(1)->id,
            'density_id' => $densities->get(1)->id,
            'availability_status' => AvailabilityStatus::MadeToOrder,
            'stock_quantity' => null,
        ]);
    }
}
