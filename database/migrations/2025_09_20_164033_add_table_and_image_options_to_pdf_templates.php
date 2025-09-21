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
            // Configurações de tamanho de imagem
            $table->integer('header_image_width')->nullable()->after('header_image_align');
            $table->integer('header_image_height')->nullable()->after('header_image_width');
            $table->integer('footer_image_width')->nullable()->after('footer_image_align');
            $table->integer('footer_image_height')->nullable()->after('footer_image_width');

            // Configurações de fontes adicionais
            $table->string('header_font_family')->default('helvetica')->after('font_family');
            $table->string('footer_font_family')->default('helvetica')->after('header_font_family');
            $table->integer('header_font_size')->default(12)->after('font_size_title');
            $table->integer('footer_font_size')->default(10)->after('header_font_size');
            $table->string('header_font_style')->nullable()->after('font_style_title');
            $table->string('footer_font_style')->nullable()->after('header_font_style');

            // Configurações da tabela
            $table->string('table_style')->default('grid')->after('body_text'); // grid, striped, minimal
            $table->string('table_header_bg')->default('#f3f4f6')->after('table_style');
            $table->string('table_header_text')->default('#374151')->after('table_header_bg');
            $table->integer('table_row_height')->default(10)->after('table_header_text');
            $table->boolean('show_table_lines')->default(true)->after('table_row_height');
            $table->boolean('use_zebra_stripes')->default(false)->after('show_table_lines');
            $table->text('table_columns')->nullable()->after('use_zebra_stripes');

            // Texto livre após a tabela
            $table->text('after_table_text')->nullable()->after('table_columns');

            // Ajustes de visualização em tempo real
            $table->boolean('real_time_preview')->default(true)->after('after_table_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_templates', function (Blueprint $table) {
            $table->dropColumn([
                'header_image_width', 'header_image_height',
                'footer_image_width', 'footer_image_height',
                'header_font_family', 'footer_font_family',
                'header_font_size', 'footer_font_size',
                'header_font_style', 'footer_font_style',
                'table_style', 'table_header_bg', 'table_header_text',
                'table_row_height', 'show_table_lines', 'use_zebra_stripes',
                'table_columns', 'after_table_text', 'real_time_preview'
            ]);
        });
    }
};
