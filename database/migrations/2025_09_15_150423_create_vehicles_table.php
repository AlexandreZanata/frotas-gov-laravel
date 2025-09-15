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
            $table->string('name', 100)->comment('Ex: FORD/RANGER XL CD4');
            $table->string('plate', 10)->unique();
            $table->string('prefix', 20)->unique();

            $table->foreignId('category_id')->nullable()->constrained('vehicle_categories')->onDelete('set null');
            $table->foreignId('current_secretariat_id')->constrained('secretariats');

            $table->decimal('fuel_tank_capacity_liters', 5, 2)->nullable();
            $table->decimal('avg_km_per_liter', 5, 2)->nullable();
            $table->enum('status', ['available', 'in_use', 'maintenance', 'blocked'])->default('available');

            // Campos para controle de troca de óleo
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
