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
        Schema::table('densities', function (Blueprint $table) {
            $table->unsignedInteger('gsm')->after('name');
            $table->unsignedInteger('sort_order')->default(0)->after('gsm');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('densities', function (Blueprint $table) {
            $table->dropColumn(['gsm', 'sort_order', 'is_active']);
        });
    }
};
