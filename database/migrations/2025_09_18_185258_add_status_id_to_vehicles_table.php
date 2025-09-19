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
        Schema::table('vehicles', function (Blueprint $table) {
            // Adicionamos a coluna como nullable() temporariamente para a migração dos dados
            $table->foreignId('vehicle_status_id')->nullable()->after('status')->constrained('vehicle_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['vehicle_status_id']);
            $table->dropColumn('vehicle_status_id');
        });
    }
}; // <-- Este '};' no final é o que provavelmente estava faltando
