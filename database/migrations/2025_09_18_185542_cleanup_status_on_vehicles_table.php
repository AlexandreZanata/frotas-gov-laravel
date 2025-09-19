<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// ...cleanup_status_on_vehicles_table.php
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Torna a nova coluna obrigatória
            $table->foreignId('vehicle_status_id')->nullable(false)->change();
            // Remove a coluna de texto antiga
            $table->dropColumn('status');
        });
    }
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->foreignId('vehicle_status_id')->nullable()->change();
        });
    }


};
