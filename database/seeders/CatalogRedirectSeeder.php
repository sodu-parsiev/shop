<?php

namespace Database\Seeders;

use App\Models\Content\Redirect;
use Illuminate\Database\Seeder;

class CatalogRedirectSeeder extends Seeder
{
    /**
     * Retired product slug => target path. Merged products point at their
     * surviving variant product; removed-outright products point at the
     * homepage catalog section (no equivalent product exists).
     *
     * @var array<string, string>
     */
    private const RETIRED_SLUG_REDIRECTS = [
        'basic-tee-155-165' => '/catalog/basic-tee-140-150',
        'basic-tee-175-185' => '/catalog/basic-tee-140-150',
        'oversize-tee-220-240' => '/catalog/oversize-tee-180',
        'hoodie-two-thread-220-240' => '/catalog/hoodie-three-thread-260-280',
        'women-tee-180' => '/#catalog',
        'sweatshirt-two-thread-220-240' => '/#catalog',
    ];

    public function run(): void
    {
        foreach (self::RETIRED_SLUG_REDIRECTS as $from => $target) {
            Redirect::query()->updateOrCreate(
                ['source_path' => "/catalog/{$from}"],
                ['target_url' => $target, 'status_code' => 301, 'is_active' => true],
            );
        }
    }
}
