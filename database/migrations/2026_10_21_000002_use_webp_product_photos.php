<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Columns holding photo paths. Bundled photos are referenced as "/brand/....jpg"
     * and were replaced by WebP copies next to them in public/brand.
     *
     * @var array<string, list<string>>
     */
    private const PHOTO_COLUMNS = [
        'products' => ['cover_image', 'og_image'],
        'product_images' => ['path'],
    ];

    /**
     * Only rewrites paths whose WebP copy exists, so admin uploads and
     * unconverted files keep working.
     */
    public function up(): void
    {
        $this->swapExtension('.jpg', '.webp');
    }

    public function down(): void
    {
        $this->swapExtension('.webp', '.jpg');
    }

    private function swapExtension(string $from, string $to): void
    {
        foreach (self::PHOTO_COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)
                    ->where($column, 'like', '/brand/%'.$from)
                    ->pluck($column, 'id')
                    ->each(function (string $path, int $id) use ($table, $column, $from, $to): void {
                        $newPath = substr($path, 0, -strlen($from)).$to;

                        if (file_exists(public_path(ltrim($newPath, '/')))) {
                            DB::table($table)->where('id', $id)->update([$column => $newPath]);
                        }
                    });
            }
        }
    }
};
