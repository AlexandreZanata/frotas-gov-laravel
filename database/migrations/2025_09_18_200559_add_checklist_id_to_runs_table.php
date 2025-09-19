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
        Schema::table('runs', function (Blueprint $table) {
            // Adiciona a coluna para a chave estrangeira do checklist
            $table->foreignId('checklist_id')
                ->nullable() // Permite que seja nulo, pois corridas antigas podem não ter
                ->after('driver_id') // Posição sugerida na tabela
                ->constrained('checklists'); // Cria a relação com a tabela 'checklists'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('runs', function (Blueprint $table) {
            $table->dropForeign(['checklist_id']);
            $table->dropColumn('checklist_id');
        });
    }
};
