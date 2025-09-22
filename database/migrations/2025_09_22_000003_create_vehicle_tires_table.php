<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('vehicle_tires')) return; // evita recriação
        Schema::create('vehicle_tires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tire_id')->constrained('tires')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('position'); // FL, FR, RL, RR, etc.
            $table->dateTime('mounted_at');
            $table->unsignedBigInteger('start_odometer_km')->nullable();
            $table->dateTime('dismounted_at')->nullable();
            $table->unsignedBigInteger('end_odometer_km')->nullable();
            $table->unsignedBigInteger('km_used')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['vehicle_id','active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_tires');
    }
};
