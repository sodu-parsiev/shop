<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Slide 09: the standalone «Печать и кастомизация» section is removed;
     * нанесение becomes one modest line inside «Производственная база», and
     * only DTF-печать/вышивка/термопечать remain as customization services
     * (Шелкография and Вшивные ярлыки are deactivated and detached, not
     * deleted — same "retire, don't destroy" pattern as the earlier density
     * and product retirements). No-op when the content row doesn't exist yet.
     */
    private const RETIRED_SERVICES = ['Шелкография', 'Вшивные ярлыки'];

    public function up(): void
    {
        $this->patchSellersNote('При необходимости организуем нанесение: DTF-печать, вышивку или термопечать.');
        $this->retireServices();
    }

    public function down(): void
    {
        $this->patchSellersNote(null);

        DB::table('customization_services')
            ->whereIn('name', self::RETIRED_SERVICES)
            ->update(['is_active' => true, 'updated_at' => now()]);
    }

    private function patchSellersNote(?string $note): void
    {
        $row = DB::table('home_page_contents')->where('id', 1)->first();

        if (! $row) {
            return;
        }

        $content = json_decode($row->content, true);

        if (! is_array($content)) {
            return;
        }

        if ($note === null) {
            unset($content['sellers']['customization_note']);
        } else {
            $content['sellers']['customization_note'] = $note;
        }

        DB::table('home_page_contents')->where('id', 1)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    private function retireServices(): void
    {
        $serviceIds = DB::table('customization_services')
            ->whereIn('name', self::RETIRED_SERVICES)
            ->pluck('id');

        if ($serviceIds->isEmpty()) {
            return;
        }

        DB::table('product_customization_service')->whereIn('customization_service_id', $serviceIds)->delete();

        DB::table('customization_services')->whereIn('id', $serviceIds)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);
    }
};
