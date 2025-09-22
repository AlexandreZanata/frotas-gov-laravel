<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'header_image',
        'footer_image',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'font_family',
        'font_size_title',
        'font_size_text',
        'font_size_table',
        'font_style_title',
        'font_style_text',

        // Campos para cabeçalho e rodapé
        'header_scope',
        'header_image_align',
        'header_text',
        'header_text_align',
        'footer_scope',
        'footer_image_align',
        'footer_text',
        'footer_text_align',
        'body_text',
        'font_family_body',

        // Novos campos para dimensões de imagem
        'header_image_width',
        'header_image_height',
        'footer_image_width',
        'footer_image_height',

        // Novas opções de fontes
        'header_font_family',
        'footer_font_family',
        'header_font_size',
        'footer_font_size',
        'header_font_style',
        'footer_font_style',

        // Configurações da tabela
        'table_style',
        'table_header_bg',
        'table_header_text',
        'table_row_height',
        'show_table_lines',
        'use_zebra_stripes',
        'table_columns',

        // Texto adicional
        'after_table_text',

        // Visualização em tempo real
        'real_time_preview',

        // Novos campos de posição vertical/inline
        'header_image_vertical_position',
        'footer_image_vertical_position',
    ];

    protected $casts = [
        'show_table_lines' => 'boolean',
        'use_zebra_stripes' => 'boolean',
        'real_time_preview' => 'boolean',
    ];
}
