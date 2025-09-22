<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('oil_change_logs')) {
            return; // tabela inexistente – nada a fazer
        }

        Schema::table('oil_change_logs', function (Blueprint $table) {
            // Relacionamentos principais (nullable para não quebrar dados antigos)
            if (!Schema::hasColumn('oil_change_logs','vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->after('id')->constrained('vehicles')->nullOnDelete()->index();
            }
            if (!Schema::hasColumn('oil_change_logs','oil_product_id')) {
                $table->foreignId('oil_product_id')->nullable()->after('vehicle_id')->constrained('oil_products')->nullOnDelete()->index();
            }
            if (!Schema::hasColumn('oil_change_logs','user_id')) {
                $table->foreignId('user_id')->nullable()->after('oil_product_id')->constrained('users')->nullOnDelete()->index();
            }

            // Dados da troca
            if (!Schema::hasColumn('oil_change_logs','change_date')) {
                $table->date('change_date')->nullable()->after('user_id')->index();
            }
            if (!Schema::hasColumn('oil_change_logs','odometer_km')) {
                $table->unsignedInteger('odometer_km')->nullable()->after('change_date');
            }
            if (!Schema::hasColumn('oil_change_logs','quantity_used')) {
                $table->decimal('quantity_used',8,2)->nullable()->after('odometer_km');
            }
            if (!Schema::hasColumn('oil_change_logs','unit_cost_at_time')) {
                $table->decimal('unit_cost_at_time',10,2)->nullable()->after('quantity_used');
            }
            if (!Schema::hasColumn('oil_change_logs','total_cost')) {
                $table->decimal('total_cost',12,2)->nullable()->after('unit_cost_at_time');
            }

            // Próxima troca
            if (!Schema::hasColumn('oil_change_logs','next_change_km')) {
                $table->unsignedInteger('next_change_km')->nullable()->after('total_cost');
            }
            if (!Schema::hasColumn('oil_change_logs','next_change_date')) {
                $table->date('next_change_date')->nullable()->after('next_change_km')->index();
            }
            if (!Schema::hasColumn('oil_change_logs','interval_km_used')) {
                $table->unsignedInteger('interval_km_used')->nullable()->after('next_change_date');
            }
            if (!Schema::hasColumn('oil_change_logs','interval_days_used')) {
                $table->unsignedInteger('interval_days_used')->nullable()->after('interval_km_used');
            }

            if (!Schema::hasColumn('oil_change_logs','notes')) {
                $table->text('notes')->nullable()->after('interval_days_used');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('oil_change_logs')) {
            return;
        }
        Schema::table('oil_change_logs', function (Blueprint $table) {
            $dropCols = [
                'vehicle_id','oil_product_id','user_id','change_date','odometer_km','quantity_used','unit_cost_at_time',
                'total_cost','next_change_km','next_change_date','interval_km_used','interval_days_used','notes'
            ];
            foreach ($dropCols as $col) {
                if (Schema::hasColumn('oil_change_logs',$col)) {
                    // Para Foreign Keys precisamos soltar a constraint antes
                    if (in_array($col,['vehicle_id','oil_product_id','user_id'])) {
                        try { $table->dropForeign(['vehicle_id']); } catch (Throwable $e) {}
                        try { $table->dropForeign(['oil_product_id']); } catch (Throwable $e) {}
                        try { $table->dropForeign(['user_id']); } catch (Throwable $e) {}
                    }
                    try { $table->dropColumn($col); } catch (Throwable $e) {}
                }
            }
        });
    }
};

