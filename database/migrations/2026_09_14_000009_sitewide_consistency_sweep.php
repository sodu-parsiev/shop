<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Slide 11: sitewide consistency sweep — hero badge/ticker move to
     * open-ended "от 100 шт. без верхнего ограничения" phrasing (dropping the
     * "5 000" ceiling), and the primary action is unified to "Запросить
     * прайс" everywhere it still said "Запросить расчёт" (sellers block,
     * process block, footer order-status legend). No-op when the content row
     * doesn't exist yet (fresh installs get these values from the seeders).
     */
    public function up(): void
    {
        $this->patch(function (array &$content): void {
            $content['hero']['hero_badge_value'] = 'от 100';
            $content['hero']['hero_badge_label'] = 'шт. без ограничений';
            $content['hero']['top_ticker'] = 'БЛАНКОВЫЙ ТЕКСТИЛЬ • ТИРАЖИ ОТ 100 ШТ. БЕЗ ВЕРХНЕГО ОГРАНИЧЕНИЯ';
            $content['sellers']['cta'] = 'Запросить прайс';
            $content['process']['cta'] = 'Запросить прайс';
            $content['footer']['order_items'][2]['label'] = 'Запросить прайс';
        });
    }

    public function down(): void
    {
        $this->patch(function (array &$content): void {
            $content['hero']['hero_badge_value'] = '100–5 000';
            $content['hero']['hero_badge_label'] = 'шт. по прайсу';
            $content['hero']['top_ticker'] = 'БЛАНКОВЫЙ ТЕКСТИЛЬ • ТИРАЖИ ОТ 100 ДО 5 000 ШТ.';
            $content['sellers']['cta'] = 'Запросить расчёт';
            $content['process']['cta'] = 'Запросить расчёт';
            $content['footer']['order_items'][2]['label'] = 'Запросить расчёт';
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
