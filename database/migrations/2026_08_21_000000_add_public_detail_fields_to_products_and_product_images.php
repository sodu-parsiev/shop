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
            $table->string('h1')->nullable()->after('name');
            $table->text('short_description')->nullable()->after('sku');
            $table->json('size_table')->nullable()->after('fit');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('alt_text')->nullable()->after('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('alt_text');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['h1', 'short_description', 'size_table']);
        });
    }
};
