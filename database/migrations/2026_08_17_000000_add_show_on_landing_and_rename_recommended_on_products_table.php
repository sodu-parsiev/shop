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
            $table->renameColumn('recommended', 'featured');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_on_landing')->default(false)->after('featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('show_on_landing');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('featured', 'recommended');
        });
    }
};
