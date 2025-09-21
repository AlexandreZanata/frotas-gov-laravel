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
        Schema::table('pdf_templates', function (Blueprint $table) {
            // Permite que as colunas sejam nulas
            $table->string('font_style_title')->nullable()->change();
            $table->string('font_style_text')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_templates', function (Blueprint $table) {
            // Reverte para não permitir nulos (caso precise desfazer)
            $table->string('font_style_title')->nullable(false)->change();
            $table->string('font_style_text')->nullable(false)->change();
        });
    }
};
