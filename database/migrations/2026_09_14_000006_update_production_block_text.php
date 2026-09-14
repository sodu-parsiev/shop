<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Slide 08: the «О производстве» block's descriptive text moves out of the
     * blade into process.intro (factory description preserving the old
     * paragraph's meaning: direct production, on-site and pre-shipment QC,
     * claims support) and the block gains a «Запросить расчёт» CTA. No-op when
     * the content row doesn't exist yet (fresh installs seed these values).
     */
    public function up(): void
    {
        $this->patchProcess([
            'intro' => 'Организуем производство крупных и кастомных партий напрямую на фабриках, которые работают под наши заказы. Учитываем требования к модели, материалу, плотности, цвету и размерному ряду. Контролируем качество на производстве и перед отгрузкой, а также сопровождаем работу с рекламациями.',
            'cta' => 'Запросить расчёт',
        ]);
    }

    public function down(): void
    {
        $this->patchProcess([
            'intro' => null,
            'cta' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function patchProcess(array $values): void
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
                unset($content['process'][$key]);
            } else {
                $content['process'][$key] = $value;
            }
        }

        DB::table('home_page_contents')->where('id', 1)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }
};
