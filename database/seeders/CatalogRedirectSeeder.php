<?php

namespace Database\Seeders;

use App\Models\Content\Redirect;
use Illuminate\Database\Seeder;

class CatalogRedirectSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const MERGED_SLUG_REDIRECTS = [
        'basic-tee-155-165' => 'basic-tee-140-150',
        'basic-tee-175-185' => 'basic-tee-140-150',
        'oversize-tee-220-240' => 'oversize-tee-180',
    ];

    public function run(): void
    {
        foreach (self::MERGED_SLUG_REDIRECTS as $from => $to) {
            Redirect::query()->updateOrCreate(
                ['source_path' => "/catalog/{$from}"],
                ['target_url' => "/catalog/{$to}", 'status_code' => 301, 'is_active' => true],
            );
        }
    }
}
