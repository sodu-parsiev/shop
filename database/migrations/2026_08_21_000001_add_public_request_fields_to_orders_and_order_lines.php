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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('request_number')->nullable()->unique()->after('id');
            $table->string('preferred_contact_method')->nullable()->after('phone');
            $table->timestamp('consent_accepted_at')->nullable()->after('message');
            $table->string('consent_ip', 45)->nullable()->after('consent_accepted_at');
            $table->string('submission_token')->nullable()->unique()->after('consent_ip');
            $table->string('landing_url')->nullable()->after('submission_token');
            $table->string('source_url')->nullable()->after('landing_url');
            $table->string('referrer_url')->nullable()->after('source_url');
            $table->string('utm_source')->nullable()->after('referrer_url');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_content')->nullable()->after('utm_campaign');
            $table->string('utm_term')->nullable()->after('utm_content');
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->string('preferred_color')->nullable()->after('preferred_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('preferred_color');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'request_number',
                'preferred_contact_method',
                'consent_accepted_at',
                'consent_ip',
                'submission_token',
                'landing_url',
                'source_url',
                'referrer_url',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
            ]);
        });
    }
};
