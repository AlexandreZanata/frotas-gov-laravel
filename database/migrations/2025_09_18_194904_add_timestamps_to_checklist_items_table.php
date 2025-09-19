<?php
// Em database/migrations/xxxx_xx_xx_xxxxxx_add_timestamps_to_checklist_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->timestamps(); // Adiciona `created_at` e `updated_at`
        });
    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropTimestamps(); // Remove as colunas se precisar reverter
        });
    }
};
