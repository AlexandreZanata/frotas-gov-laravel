<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('oil_products')) return;

        Schema::table('oil_products', function (Blueprint $table) {
            if (!Schema::hasColumn('oil_products','name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('oil_products','code')) {
                $table->string('code')->nullable()->after('name')->unique();
            }
            if (!Schema::hasColumn('oil_products','brand')) {
                $table->string('brand')->nullable()->after('code');
            }
            if (!Schema::hasColumn('oil_products','viscosity')) {
                $table->string('viscosity')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('oil_products','stock_quantity')) {
                $table->unsignedInteger('stock_quantity')->nullable()->after('viscosity');
            }
            if (!Schema::hasColumn('oil_products','reorder_level')) {
                $table->unsignedInteger('reorder_level')->nullable()->after('stock_quantity');
            }
            if (!Schema::hasColumn('oil_products','unit_cost')) {
                $table->decimal('unit_cost',10,2)->nullable()->after('reorder_level');
            }
            if (!Schema::hasColumn('oil_products','recommended_interval_km')) {
                $table->unsignedInteger('recommended_interval_km')->nullable()->after('unit_cost');
            }
            if (!Schema::hasColumn('oil_products','recommended_interval_days')) {
                $table->unsignedInteger('recommended_interval_days')->nullable()->after('recommended_interval_km');
            }
            if (!Schema::hasColumn('oil_products','description')) {
                $table->text('description')->nullable()->after('recommended_interval_days');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('oil_products')) return;
        Schema::table('oil_products', function (Blueprint $table) {
            $cols = ['name','code','brand','viscosity','stock_quantity','reorder_level','unit_cost','recommended_interval_km','recommended_interval_days','description'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('oil_products',$col)) {
                    try { $table->dropColumn($col); } catch (Throwable $e) {}
                }
            }
        });
    }
};

