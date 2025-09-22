<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Garante colunas de oil_products
        if (Schema::hasTable('oil_products')) {
            Schema::table('oil_products', function (Blueprint $table) {
                $addString = function(string $col, int $len = 255) use ($table) {
                    if (!Schema::hasColumn('oil_products',$col)) { $table->string($col, $len)->nullable(); }
                };
                $addInt = function(string $col) use ($table) { if (!Schema::hasColumn('oil_products',$col)) { $table->unsignedInteger($col)->nullable(); } };
                $addDecimal = function(string $col,$p,$s) use ($table) { if (!Schema::hasColumn('oil_products',$col)) { $table->decimal($col,$p,$s)->nullable(); } };

                $addString('name');
                $addString('code',50);
                if (Schema::hasColumn('oil_products','code')) { try { $table->unique('code'); } catch (Throwable $e) {} }
                $addString('brand',100);
                $addString('viscosity',50);
                $addInt('stock_quantity');
                $addInt('reorder_level');
                $addDecimal('unit_cost',10,2);
                $addInt('recommended_interval_km');
                $addInt('recommended_interval_days');
                if (!Schema::hasColumn('oil_products','description')) { $table->text('description')->nullable(); }
            });
        }

        // Índices para oil_change_logs
        if (Schema::hasTable('oil_change_logs')) {
            Schema::table('oil_change_logs', function (Blueprint $table) {
                if (Schema::hasColumn('oil_change_logs','vehicle_id') && Schema::hasColumn('oil_change_logs','change_date')) {
                    try { $table->index(['vehicle_id','change_date'],'oil_logs_vehicle_date_idx'); } catch (Throwable $e) {}
                }
                if (Schema::hasColumn('oil_change_logs','oil_product_id')) {
                    try { $table->index('oil_product_id','oil_logs_product_idx'); } catch (Throwable $e) {}
                }
                if (Schema::hasColumn('oil_change_logs','change_date')) {
                    try { $table->index('change_date','oil_logs_date_idx'); } catch (Throwable $e) {}
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('oil_change_logs')) {
            Schema::table('oil_change_logs', function (Blueprint $table) {
                foreach (['oil_logs_vehicle_date_idx','oil_logs_product_idx','oil_logs_date_idx'] as $idx) {
                    try { $table->dropIndex($idx); } catch (Throwable $e) {}
                }
            });
        }
        // Não removemos colunas em down para evitar perda de dados
    }
};

