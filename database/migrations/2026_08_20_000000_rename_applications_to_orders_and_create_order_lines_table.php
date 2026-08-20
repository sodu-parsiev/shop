<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('applications') && ! Schema::hasTable('orders')) {
            Schema::rename('applications', 'orders');
        }

        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('category_name')->nullable();
            $table->string('availability_label');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('product_moq');
            $table->timestamps();
        });

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')
                ->whereIn('name', $this->applicationPermissionNames())
                ->update(['name' => DB::raw("replace(name, 'application', 'order')")]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')
                ->whereIn('name', $this->orderPermissionNames())
                ->update(['name' => DB::raw("replace(name, 'order', 'application')")]);
        }

        Schema::dropIfExists('order_lines');

        if (Schema::hasTable('orders') && ! Schema::hasTable('applications')) {
            Schema::rename('orders', 'applications');
        }
    }

    /**
     * @return array<int, string>
     */
    private function applicationPermissionNames(): array
    {
        return [
            'view_any_application',
            'view_application',
            'create_application',
            'update_application',
            'delete_application',
            'delete_any_application',
            'export_application',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function orderPermissionNames(): array
    {
        return [
            'view_any_order',
            'view_order',
            'create_order',
            'update_order',
            'delete_order',
            'delete_any_order',
            'export_order',
        ];
    }
};
