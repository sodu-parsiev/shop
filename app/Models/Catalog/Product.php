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
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'category_id',
    'slug',
    'sku',
    'description',
    'composition',
    'fit',
    'moq',
    'stock_conditions',
    'availability_status',
    'stock_quantity',
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

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name.'-'.$product->sku);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'moq' => 'integer',
            'featured' => 'boolean',
            'show_on_landing' => 'boolean',
            'sort_order' => 'integer',
            'status' => ProductStatus::class,
            'availability_status' => AvailabilityStatus::class,
            'stock_quantity' => 'integer',
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

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_color')->orderBy('sort_order');
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_size')->orderBy('sort_order');
    }

    public function densities(): BelongsToMany
    {
        return $this->belongsToMany(Density::class, 'product_density')->orderBy('gsm');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function isInStock(): bool
    {
        return $this->availability_status === AvailabilityStatus::InStock
            && ($this->stock_quantity ?? 0) > 0;
    }
}
