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
        Schema::create('oil_change_logs', function (Blueprint $table) {
            $table->id();
            // Relacionamentos
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            // removido foreign constraint direta para oil_products para evitar ordem de migração; será adicionada em migração posterior
            $table->unsignedBigInteger('oil_product_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuário que registrou');

            // Dados da troca
            $table->date('change_date');
            $table->unsignedInteger('odometer_km');
            $table->decimal('quantity_used', 8, 2)->default(0)->comment('Litros usados');
            $table->decimal('unit_cost_at_time', 10, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();

            // Próxima troca calculada
            $table->unsignedInteger('next_change_km')->nullable();
            $table->date('next_change_date')->nullable();
            $table->unsignedInteger('interval_km_used')->nullable();
            $table->unsignedInteger('interval_days_used')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->index('oil_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oil_change_logs');
    }
};
