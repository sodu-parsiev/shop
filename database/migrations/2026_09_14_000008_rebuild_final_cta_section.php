<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Slide 10: the final form section is rebuilt around one primary action
     * (requesting the price list) — a single heading replaces the old
     * heading_main/heading_accent split, plus new subcopy, a trust line, and
     * a renamed submit button. No-op when the content row doesn't exist yet
     * (fresh installs get these values from the seeder).
     */
    public function up(): void
    {
        $this->patch(function (array &$content): void {
            unset($content['cta_section']['heading_main'], $content['cta_section']['heading_accent']);
            $content['cta_section']['heading'] = 'Получите оптовый прайс и актуальное наличие';
            $content['cta_section']['subcopy'] = 'Подберём изделия под ваш тираж и задачу, сообщим стоимость и сроки поставки. Работаем с заказами от 100 шт. без верхнего ограничения по объёму.';
            $content['cta_section']['trust_line'] = 'Склад в Москве • Оплата по расчётному счёту • Полный комплект документов';
            $content['form']['submit'] = 'Запросить прайс';
        });
    }

    public function down(): void
    {
        $this->patch(function (array &$content): void {
            unset($content['cta_section']['heading'], $content['cta_section']['trust_line']);
            $content['cta_section']['heading_main'] = 'Пора сделать';
            $content['cta_section']['heading_accent'] = 'свой ход.';
            $content['cta_section']['subcopy'] = 'Сформируйте черновик заявки: выберите товары в каталоге, укажите количество по каждой позиции и оставьте контакты для расчёта.';
            $content['form']['submit'] = 'Сформировать заявку';
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
