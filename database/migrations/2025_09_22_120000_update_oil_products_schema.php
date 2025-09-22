<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('oil_products')) return; // segurança
        Schema::table('oil_products', function (Blueprint $table) {
            if (!Schema::hasColumn('oil_products','name')) $table->string('name',255)->nullable()->after('id');
            if (!Schema::hasColumn('oil_products','code')) $table->string('code',50)->nullable()->after('name');
            if (!Schema::hasColumn('oil_products','brand')) $table->string('brand',100)->nullable()->after('code');
            if (!Schema::hasColumn('oil_products','viscosity')) $table->string('viscosity',50)->nullable()->after('brand');
            if (!Schema::hasColumn('oil_products','stock_quantity')) $table->unsignedInteger('stock_quantity')->default(0)->after('viscosity');
            if (!Schema::hasColumn('oil_products','reorder_level')) $table->unsignedInteger('reorder_level')->default(0)->after('stock_quantity');
            if (!Schema::hasColumn('oil_products','unit_cost')) $table->decimal('unit_cost',10,2)->default(0)->after('reorder_level');
            if (!Schema::hasColumn('oil_products','recommended_interval_km')) $table->unsignedInteger('recommended_interval_km')->nullable()->after('unit_cost');
            if (!Schema::hasColumn('oil_products','recommended_interval_days')) $table->unsignedInteger('recommended_interval_days')->nullable()->after('recommended_interval_km');
            if (!Schema::hasColumn('oil_products','description')) $table->text('description')->nullable()->after('recommended_interval_days');
            if (Schema::hasColumn('oil_products','code')) {
                try { $table->unique('code'); } catch (Throwable $e) {}
            }
        });
    }

    public function down(): void
    {
        // Não removemos colunas para não perder dados.
    }
};

