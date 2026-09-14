<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NEW_UNIQUE_INDEX = 'product_price_tiers_density_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->foreignId('density_id')->nullable()->after('product_id')
                ->constrained()->restrictOnDelete();
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            // Add the new composite unique first — it also starts with `product_id`, so it can take
            // over as the index backing the `product_id` foreign key once the old one is dropped below.
            // MySQL refuses to drop an index that's the only one currently supporting an FK.
            $table->unique(['product_id', 'density_id', 'quantity', 'currency'], self::NEW_UNIQUE_INDEX);
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'quantity', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Same ordering concern as up(): re-add the old unique (which also starts with
        // `product_id`) before dropping the new one, or MySQL refuses to drop the only
        // index currently backing the `product_id` foreign key.
        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->unique(['product_id', 'quantity', 'currency']);
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->dropUnique(self::NEW_UNIQUE_INDEX);
            $table->dropConstrainedForeignId('density_id');
        });
    }
};
