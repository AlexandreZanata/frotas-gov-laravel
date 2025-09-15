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
        Schema::create('fuelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('runs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('secretariat_id')->nullable()->constrained('secretariats')->onDelete('set null');
            $table->foreignId('gas_station_id')->nullable()->constrained('gas_stations')->onDelete('set null');
            $table->foreignId('fuel_type_id')->nullable()->constrained('fuel_types')->onDelete('set null');

            $table->string('gas_station_name', 150)->nullable()->comment('Para abastecimento manual');
            $table->unsignedInteger('km');
            $table->decimal('liters', 10, 2);
            $table->decimal('total_value', 10, 2)->nullable();
            $table->string('invoice_path')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuelings');
    }
};
