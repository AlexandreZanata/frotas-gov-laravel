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
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return; // skip for sqlite tests
        }
        Schema::table('vehicles', function (Blueprint $table) {
            // Primeiro, torna a nova coluna obrigatória (não-nula)
            $table->foreignId('fuel_type_id')->nullable(false)->change();

            // Depois, remove a coluna de texto antiga
            $table->dropColumn('fuel_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return; // skip revert for sqlite
        }
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('fuel_type')->nullable(); // Ajuste o after se necessário
            $table->foreignId('fuel_type_id')->nullable()->change();
        });
    }
};
