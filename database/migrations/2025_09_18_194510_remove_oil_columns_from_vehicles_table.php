<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// ...remove_oil_columns_from_vehicles_table.php
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'last_oil_change_km',
                'last_oil_change_date',
                'next_oil_change_km',
                'next_oil_change_date',
            ]);
        });
    }

    public function down(): void // Para poder reverter, se necessário
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('last_oil_change_km')->nullable();
            $table->date('last_oil_change_date')->nullable();
            $table->integer('next_oil_change_km')->nullable();
            $table->date('next_oil_change_date')->nullable();
        });
    }
};
