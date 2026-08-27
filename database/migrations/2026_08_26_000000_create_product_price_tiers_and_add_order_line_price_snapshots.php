<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->string('currency', 3)->default('RUB');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'quantity', 'currency']);
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->after('product_moq');
            $table->string('currency', 3)->nullable()->after('unit_price');
            $table->unsignedInteger('price_quantity_tier')->nullable()->after('currency');
            $table->string('price_note')->nullable()->after('price_quantity_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn([
                'unit_price',
                'currency',
                'price_quantity_tier',
                'price_note',
            ]);
        });

        Schema::dropIfExists('product_price_tiers');
    }
};
