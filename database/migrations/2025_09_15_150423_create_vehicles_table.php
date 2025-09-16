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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // Campos do formulário (substituindo 'name')
            $table->string('brand', 100)->comment('Marca do veículo, Ex: FORD');
            $table->string('model', 100)->comment('Modelo do veículo, Ex: RANGER');
            $table->year('year');
            $table->string('plate', 10)->unique();
            $table->string('renavam', 11)->unique();

            // Campos mantidos da versão original
            $table->string('prefix', 20)->unique()->nullable();

            // Campos do formulário
            $table->unsignedInteger('current_km');
            $table->string('fuel_type', 50);
            $table->string('document_path')->nullable();

            // Relacionamentos
            $table->foreignId('category_id')->nullable()->constrained('vehicle_categories')->onDelete('set null');
            $table->foreignId('current_secretariat_id')->constrained('secretariats');
            $table->foreignId('current_department_id')->nullable()->constrained('departments')->onDelete('set null');

            // Campos mantidos da versão original (com nome ajustado)
            $table->decimal('tank_capacity', 8, 2)->comment('Capacidade do tanque em litros');
            $table->decimal('avg_km_per_liter', 5, 2)->nullable();

            // Status (ajustado para corresponder ao formulário)
            $table->enum('status', ['Disponível', 'Em uso', 'Manutenção', 'Inativo'])->default('Disponível');

            // Campos para controle de troca de óleo (mantidos da versão original)
            $table->unsignedInteger('last_oil_change_km')->nullable();
            $table->date('last_oil_change_date')->nullable();
            $table->unsignedInteger('next_oil_change_km')->nullable();
            $table->date('next_oil_change_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
