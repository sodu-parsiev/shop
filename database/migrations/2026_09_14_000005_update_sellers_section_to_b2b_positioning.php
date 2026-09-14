<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Slide 07: the marketplace-sellers block becomes universal B2B
     * positioning ("Производственная база"). Patches the sellers.* keys of
     * the home page content blob in place; the seeders carry the same values
     * for fresh installs. No-op when the content row doesn't exist yet.
     */
    public function up(): void
    {
        $this->patchSellers([
            'eyebrow' => 'Производственная база',
            'heading' => 'Изготовим партию',
            'subheading' => 'под ваш бренд и требования',
            'intro' => 'Помогаем подготовить крупную партию: согласуем размерную матрицу, упаковку, этикетки и маркировку.',
            'cta' => 'Запросить расчёт',
            'items' => [
                ['label' => 'Футболки, лонгсливы, худи и другие базовые изделия'],
                ['label' => 'Заказ от 100 шт.'],
                ['label' => 'Крупные партии без верхнего ограничения'],
                ['label' => 'Плотности из прайса как отдельные модели'],
                ['label' => 'Упаковка, этикетки и маркировка'],
                ['label' => 'Повтор серии по образцу'],
            ],
        ]);
    }

    public function down(): void
    {
        $this->patchSellers([
            'eyebrow' => 'Для продавцов маркетплейсов',
            'heading' => 'Не просто поставщик.',
            'subheading' => 'Производственная база.',
            'intro' => null,
            'cta' => 'Обсудить техническое задание',
            'items' => [
                ['label' => 'Футболки, лонгсливы, свитшоты и худи'],
                ['label' => 'Цены по тиражам от 100 до 5 000 штук'],
                ['label' => 'Плотности из прайса как отдельные модели'],
                ['label' => 'Упаковка, этикетки и маркировка'],
                ['label' => 'Повтор серии по спецификации'],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function patchSellers(array $values): void
    {
        $row = DB::table('home_page_contents')->where('id', 1)->first();

        if (! $row) {
            return;
        }

        $content = json_decode($row->content, true);

        if (! is_array($content)) {
            return;
        }

        foreach ($values as $key => $value) {
            if ($value === null) {
                unset($content['sellers'][$key]);
            } else {
                $content['sellers'][$key] = $value;
            }
        }

        DB::table('home_page_contents')->where('id', 1)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }
};
