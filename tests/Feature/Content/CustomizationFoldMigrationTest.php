<?php

use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Product;
use App\Models\Content\HomePageContent;
use Illuminate\Support\Facades\DB;

function seedPreCustomizationFoldState(): void
{
    HomePageContent::query()->create(['content' => [
        'sellers' => ['eyebrow' => 'Производственная база', 'intro' => 'Помогаем подготовить крупную партию.'],
    ]]);

    $product = Product::factory()->create();

    foreach (['Шелкография', 'Вышивка', 'DTF / термопечать', 'Вшивные ярлыки'] as $name) {
        $service = CustomizationService::factory()->create(['name' => $name, 'is_active' => true]);
        $product->customizationServices()->attach($service);
    }
}

test('the fold migration adds the customization note and retires the extra services', function () {
    seedPreCustomizationFoldState();

    $migration = include database_path('migrations/2026_09_14_000007_fold_customization_into_production_block.php');
    $migration->up();

    $content = HomePageContent::query()->firstOrFail();
    expect($content->get('sellers.customization_note'))->toBe('При необходимости организуем нанесение: DTF-печать, вышивку или термопечать.');
    expect($content->get('sellers.intro'))->toBe('Помогаем подготовить крупную партию.');

    foreach (['Шелкография', 'Вшивные ярлыки'] as $name) {
        $service = CustomizationService::where('name', $name)->firstOrFail();
        expect($service->is_active)->toBeFalse();
        expect(DB::table('product_customization_service')->where('customization_service_id', $service->id)->exists())->toBeFalse();
    }

    foreach (['Вышивка', 'DTF / термопечать'] as $name) {
        $service = CustomizationService::where('name', $name)->firstOrFail();
        expect($service->is_active)->toBeTrue();
        expect(DB::table('product_customization_service')->where('customization_service_id', $service->id)->exists())->toBeTrue();
    }
});

test('the fold migration is idempotent', function () {
    seedPreCustomizationFoldState();

    $migration = include database_path('migrations/2026_09_14_000007_fold_customization_into_production_block.php');
    $migration->up();
    $migration->up();

    expect(HomePageContent::query()->firstOrFail()->get('sellers.customization_note'))
        ->toBe('При необходимости организуем нанесение: DTF-печать, вышивку или термопечать.');
    expect(CustomizationService::where('name', 'Шелкография')->value('is_active'))->toBeFalse();
});

test('the fold migration is a no-op when no content row exists', function () {
    $migration = include database_path('migrations/2026_09_14_000007_fold_customization_into_production_block.php');

    $migration->up();

    expect(HomePageContent::query()->count())->toBe(0);
    expect(CustomizationService::query()->count())->toBe(0);
});
