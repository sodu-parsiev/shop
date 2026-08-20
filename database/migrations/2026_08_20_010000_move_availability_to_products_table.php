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
        Schema::table('products', function (Blueprint $table) {
            $table->string('availability_status')->default('made_to_order')->after('stock_conditions');
            $table->unsignedInteger('stock_quantity')->nullable()->after('availability_status');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['availability_status', 'stock_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('availability_status')->default('made_to_order')->after('density_id');
            $table->unsignedInteger('stock_quantity')->nullable()->after('availability_status');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['availability_status', 'stock_quantity']);
        });
    }
};
