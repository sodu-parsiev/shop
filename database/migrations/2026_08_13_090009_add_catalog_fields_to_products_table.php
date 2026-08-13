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
            $table->string('slug')->unique()->after('name');
            $table->string('sku')->unique()->after('slug');
            $table->text('short_description')->nullable()->after('sku');
            $table->text('description')->nullable()->after('short_description');
            $table->text('composition')->nullable()->after('description');
            $table->string('fit')->nullable()->after('composition');
            // MOQ is per-product and must be validated against every selected
            // variant/position independently by the public app - never summed.
            $table->unsignedInteger('moq')->default(1)->after('fit');
            $table->text('stock_conditions')->nullable()->after('moq');
            $table->string('status')->default('active')->after('stock_conditions');
            $table->boolean('recommended')->default(false)->after('status');
            $table->unsignedInteger('sort_order')->default(0)->after('recommended');
            $table->string('meta_title')->nullable()->after('sort_order');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('canonical_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'sku',
                'short_description',
                'description',
                'composition',
                'fit',
                'moq',
                'stock_conditions',
                'status',
                'recommended',
                'sort_order',
                'meta_title',
                'meta_description',
                'canonical_url',
                'og_image',
            ]);
        });
    }
};
