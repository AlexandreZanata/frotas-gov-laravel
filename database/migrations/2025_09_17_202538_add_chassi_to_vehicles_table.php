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
            // Adiciona a coluna `chassi` (varchar de 50) que pode ser nula
            // A colocamos depois da coluna `renavam` para organização
            $table->string('chassi', 50)->nullable()->after('renavam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Remove a coluna `chassi` caso a migration seja revertida
            $table->dropColumn('chassi');
        });
    }
};
