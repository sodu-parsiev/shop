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
        Schema::table('faqs', function (Blueprint $table) {
            $table->string('question')->after('id');
            $table->text('answer')->after('question');
            $table->unsignedInteger('sort_order')->default(0)->after('answer');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question', 'answer', 'sort_order', 'is_active']);
        });
    }
};
