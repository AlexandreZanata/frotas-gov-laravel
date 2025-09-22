<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tires')) {
            Schema::create('tires', function (Blueprint $table) {
                $table->id();
                $table->string('serial_number')->unique();
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->string('dimension')->nullable();
                $table->date('purchase_date')->nullable();
                $table->decimal('initial_tread_depth_mm',5,2)->nullable();
                $table->decimal('current_tread_depth_mm',5,2)->nullable();
                $table->unsignedInteger('expected_tread_life_km')->nullable();
                $table->unsignedBigInteger('accumulated_km')->default(0);
                $table->enum('status',[ 'stock','in_use','recap','recap_in','recap_out','discarded','attention','critical' ])->default('stock');
                $table->foreignId('current_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->string('position')->nullable();
                $table->dateTime('installed_at')->nullable();
                $table->dateTime('removed_at')->nullable();
                $table->unsignedTinyInteger('life_cycles')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
            return;
        }
        // Se a tabela já existe (migração antiga minimal), adiciona colunas ausentes
        Schema::table('tires', function (Blueprint $table) {
            $add = fn($col, $closure) => Schema::hasColumn('tires',$col) ?: $closure();
            $add('serial_number', fn()=> $table->string('serial_number')->unique());
            $add('brand', fn()=> $table->string('brand')->nullable());
            $add('model', fn()=> $table->string('model')->nullable());
            $add('dimension', fn()=> $table->string('dimension')->nullable());
            $add('purchase_date', fn()=> $table->date('purchase_date')->nullable());
            $add('initial_tread_depth_mm', fn()=> $table->decimal('initial_tread_depth_mm',5,2)->nullable());
            $add('current_tread_depth_mm', fn()=> $table->decimal('current_tread_depth_mm',5,2)->nullable());
            $add('expected_tread_life_km', fn()=> $table->unsignedInteger('expected_tread_life_km')->nullable());
            $add('accumulated_km', fn()=> $table->unsignedBigInteger('accumulated_km')->default(0));
            if (!Schema::hasColumn('tires','status')) {
                $table->enum('status',[ 'stock','in_use','recap','recap_in','recap_out','discarded','attention','critical' ])->default('stock');
            }
            if (!Schema::hasColumn('tires','current_vehicle_id')) {
                $table->foreignId('current_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            }
            $add('position', fn()=> $table->string('position')->nullable());
            $add('installed_at', fn()=> $table->dateTime('installed_at')->nullable());
            $add('removed_at', fn()=> $table->dateTime('removed_at')->nullable());
            $add('life_cycles', fn()=> $table->unsignedTinyInteger('life_cycles')->default(0));
            $add('notes', fn()=> $table->text('notes')->nullable());
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tires');
    }
};
