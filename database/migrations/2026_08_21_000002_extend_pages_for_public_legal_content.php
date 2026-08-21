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
        Schema::table('pages', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('title')->nullable()->after('slug');
            $table->longText('body')->nullable()->after('title');
            $table->string('page_type')->default('content')->after('body');
            $table->boolean('is_published')->default(true)->after('page_type');
            $table->unsignedInteger('sort_order')->default(0)->after('is_published');
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
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'title',
                'body',
                'page_type',
                'is_published',
                'sort_order',
                'meta_title',
                'meta_description',
                'canonical_url',
                'og_image',
            ]);
        });
    }
};
