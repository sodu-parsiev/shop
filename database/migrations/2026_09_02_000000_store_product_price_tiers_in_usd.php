<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const RUB_TO_USD_RATE = '80.0';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->setCurrencyDefault('USD');

        DB::table('product_price_tiers')
            ->where('currency', 'RUB')
            ->update([
                'unit_price' => DB::raw('ROUND(unit_price / '.self::RUB_TO_USD_RATE.', 2)'),
                'currency' => 'USD',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setCurrencyDefault('RUB');

        DB::table('product_price_tiers')
            ->where('currency', 'USD')
            ->update([
                'unit_price' => DB::raw('ROUND(unit_price * '.self::RUB_TO_USD_RATE.', 2)'),
                'currency' => 'RUB',
                'updated_at' => now(),
            ]);
    }

    private function setCurrencyDefault(string $currency): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE product_price_tiers MODIFY currency VARCHAR(3) NOT NULL DEFAULT '{$currency}'");
    }
};
