<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_ADDRESS = 'Московская область, Котельники, Дзержинское шоссе, 13';

    private const NEW_ADDRESS = 'г. Москва, ул. Вишнёвая, д. 9, корп. 1, бизнес-центр «Империал»';

    /**
     * The contacts block, footer and LocalBusiness markup show the actual
     * (office) address; the Ivanovo legal address stays on the legal pages
     * only. No-op when the content row doesn't exist yet (fresh installs
     * get the new value from the seeder).
     */
    public function up(): void
    {
        $this->patch(function (array &$content): void {
            $content['cta_section']['address'] = self::NEW_ADDRESS;
        });
    }

    public function down(): void
    {
        $this->patch(function (array &$content): void {
            $content['cta_section']['address'] = self::OLD_ADDRESS;
        });
    }

    private function patch(callable $mutate): void
    {
        $row = DB::table('home_page_contents')->where('id', 1)->first();

        if (! $row) {
            return;
        }

        $content = json_decode($row->content, true);

        if (! is_array($content)) {
            return;
        }

        $mutate($content);

        DB::table('home_page_contents')->where('id', 1)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }
};
