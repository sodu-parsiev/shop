<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The final CTA contact email was seeded with a typo'd domain
     * (svojkhod.ru instead of svoyhod.ru). No-op when the content row
     * doesn't exist yet (fresh installs get the corrected value from the
     * seeder).
     */
    public function up(): void
    {
        $this->patch(function (array &$content): void {
            $content['cta_section']['email'] = 'info@svoyhod.ru';
        });
    }

    public function down(): void
    {
        $this->patch(function (array &$content): void {
            $content['cta_section']['email'] = 'info@svojkhod.ru';
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
