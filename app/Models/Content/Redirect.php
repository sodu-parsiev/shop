<?php

namespace App\Models\Content;

use Database\Factories\Content\RedirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'source_path',
    'target_url',
    'status_code',
    'is_active',
    'hits',
    'last_used_at',
])]
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
            'hits' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }
}
