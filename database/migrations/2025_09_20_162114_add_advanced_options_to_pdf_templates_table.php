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
            // Opções do Cabeçalho
            $table->string('header_scope')->default('all')->after('footer_image'); // all, first, none
            $table->string('header_image_align')->default('C')->after('header_scope'); // L, C, R
            $table->text('header_text')->nullable()->after('header_image_align');
            $table->string('header_text_align')->default('C')->after('header_text'); // L, C, R

            // Opções do Rodapé
            $table->string('footer_scope')->default('all')->after('header_text_align'); // all, first, none
            $table->string('footer_image_align')->default('C')->after('footer_scope');
            $table->text('footer_text')->nullable()->after('footer_image_align');
            $table->string('footer_text_align')->default('C')->after('footer_text');

            // Opções do Corpo do Texto
            $table->text('body_text')->nullable()->after('footer_text_align');

            // Adiciona a coluna de família da fonte para o corpo
            $table->string('font_family_body')->default('helvetica')->after('font_style_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_templates', function (Blueprint $table) {
            $table->dropColumn([
                'header_scope', 'header_image_align', 'header_text', 'header_text_align',
                'footer_scope', 'footer_image_align', 'footer_text', 'footer_text_align',
                'body_text', 'font_family_body'
            ]);
        });
    }
};
