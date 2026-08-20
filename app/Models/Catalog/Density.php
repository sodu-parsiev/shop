<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\DensityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'gsm', 'sort_order', 'is_active'])]
class Density extends Model
{
    /** @use HasFactory<DensityFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'gsm' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_density');
    }
}
