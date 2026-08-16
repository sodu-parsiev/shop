<?php

namespace App\Models\Catalog;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use Database\Factories\Catalog\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'name',
    'category_id',
    'slug',
    'sku',
    'short_description',
    'description',
    'composition',
    'fit',
    'moq',
    'stock_conditions',
    'status',
    'featured',
    'show_on_landing',
    'sort_order',
    'meta_title',
    'meta_description',
    'canonical_url',
    'og_image',
    'cover_image',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'moq' => 'integer',
            'featured' => 'boolean',
            'show_on_landing' => 'boolean',
            'sort_order' => 'integer',
            'status' => ProductStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function customizationServices(): BelongsToMany
    {
        return $this->belongsToMany(CustomizationService::class, 'product_customization_service');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function isInStock(): bool
    {
        return $this->variants->contains(
            fn (ProductVariant $variant) => $variant->availability_status === AvailabilityStatus::InStock
                && ($variant->stock_quantity ?? 0) > 0
        );
    }

    /**
     * @return Collection<int, Color>
     */
    public function distinctColors(): Collection
    {
        return $this->variants
            ->pluck('color')
            ->filter()
            ->unique('id')
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * @return Collection<int, Density>
     */
    public function distinctDensities(): Collection
    {
        return $this->variants
            ->pluck('density')
            ->filter()
            ->unique('id')
            ->sortBy('gsm')
            ->values();
    }
}
