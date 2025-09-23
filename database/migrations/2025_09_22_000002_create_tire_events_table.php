<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tire_events')) {
            return; // já criada anteriormente
        }
        Schema::create('tire_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tire_id')->constrained('tires')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->enum('type', [
                'install','remove','rotation_internal','rotation_external_out','rotation_external_in','replacement','recap_sent','recap_returned','discard'
            ]);
            $table->foreignId('from_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('to_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('from_position')->nullable();
            $table->string('to_position')->nullable();
            $table->unsignedBigInteger('odometer_km')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('tire_events')) {
            Schema::dropIfExists('tire_events');
        }
    }
};
