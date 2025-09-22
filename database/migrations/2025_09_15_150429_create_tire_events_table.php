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
        if (Schema::hasTable('tire_events')) {
            // Caso já exista com estrutura antiga mínima, tentar adicionar colunas se faltarem
            Schema::table('tire_events', function (Blueprint $table) {
                if (!Schema::hasColumn('tire_events','tire_id')) {
                    $table->foreignId('tire_id')->after('id')->constrained('tires')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('tire_events','user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('tire_events','vehicle_id')) {
                    $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                }
                if (!Schema::hasColumn('tire_events','type')) {
                    $table->enum('type', [
                        'install','remove','rotation_internal','rotation_external_out','rotation_external_in','replacement','recap_sent','recap_returned','discard'
                    ])->after('vehicle_id');
                }
                if (!Schema::hasColumn('tire_events','from_vehicle_id')) {
                    $table->foreignId('from_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                }
                if (!Schema::hasColumn('tire_events','to_vehicle_id')) {
                    $table->foreignId('to_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                }
                if (!Schema::hasColumn('tire_events','from_position')) {
                    $table->string('from_position')->nullable();
                }
                if (!Schema::hasColumn('tire_events','to_position')) {
                    $table->string('to_position')->nullable();
                }
                if (!Schema::hasColumn('tire_events','odometer_km')) {
                    $table->unsignedBigInteger('odometer_km')->nullable();
                }
                if (!Schema::hasColumn('tire_events','notes')) {
                    $table->text('notes')->nullable();
                }
            });
            return;
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tire_events');
    }
};
