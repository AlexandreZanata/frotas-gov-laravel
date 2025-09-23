<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adiciona comentário à coluna scope para documentar os valores possíveis
        DB::statement("ALTER TABLE chat_message_templates MODIFY COLUMN scope ENUM('global', 'secretariat', 'personal') COMMENT 'Escopo do template: global, secretariat (específico de secretaria), personal (uso pessoal)'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volta para o estado anterior (apenas global e secretariat)
        DB::statement("ALTER TABLE chat_message_templates MODIFY COLUMN scope ENUM('global', 'secretariat') COMMENT 'Escopo do template: global ou secretariat (específico de secretaria)'");
    }
};
