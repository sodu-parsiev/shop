<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

#[Fillable(['content'])]
class HomePageContent extends Model
{
    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->content ?? [], $key, $default);
    }
}
