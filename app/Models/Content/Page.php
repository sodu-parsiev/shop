<?php

namespace App\Models\Content;

use Database\Factories\Content\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'title',
    'body',
    'page_type',
    'is_published',
    'sort_order',
    'meta_title',
    'meta_description',
    'canonical_url',
    'og_image',
])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            if (blank($page->slug) && filled($page->name)) {
                $page->slug = Str::slug($page->name);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
