<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds a phone number to the "Контакты" block, and WhatsApp/Telegram
     * labels for the "Как связаться" field. No-op when the content row
     * doesn't exist yet (fresh installs get these from the seeder).
     */
    public function up(): void
    {
        $this->patch(function (array &$content): void {
            $content['cta_section']['phone_label'] ??= 'Телефон:';
            $content['cta_section']['phone'] ??= '+7 913 065-11-11';
            $content['form']['contact_whatsapp'] ??= 'WhatsApp';
            $content['form']['contact_telegram'] ??= 'Telegram';
        });
    }

    public function down(): void
    {
        $this->patch(function (array &$content): void {
            unset(
                $content['cta_section']['phone_label'],
                $content['cta_section']['phone'],
                $content['form']['contact_whatsapp'],
                $content['form']['contact_telegram'],
            );
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
