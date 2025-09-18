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
        Schema::create('runs', function (Blueprint $table) {
            $table->id();
            // REMOVEMOS a foreign key para 'checklists' daqui.

            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('driver_id')->constrained('users');
            $table->foreignId('secretariat_id')->nullable()->constrained('secretariats')->onDelete('set null');

            $table->unsignedInteger('start_km')->nullable();
            $table->unsignedInteger('end_km')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('destination')->nullable();
            $table->string('stop_point')->nullable();

            // Coluna status com todos os valores necessários
            $table->enum('status', ['pending_start', 'in_progress', 'completed'])->default('pending_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runs');
    }
};
