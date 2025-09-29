<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'header_image',
        'footer_image',
        'header_scope',
        'header_image_align',
        'header_image_width',
        'header_image_height',
        'header_text',
        'header_text_align',
        'footer_scope',
        'footer_image_align',
        'footer_image_width',
        'footer_image_height',
        'footer_text',
        'footer_text_align',
        'body_text',
        'after_table_text',
        'table_style',
        'table_header_bg',
        'table_header_text',
        'table_row_height',
        'show_table_lines',
        'use_zebra_stripes',
        'table_columns',
        'real_time_preview',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'font_family',
        'font_family_body',
        'header_font_family',
        'footer_font_family',
        'font_size_title',
        'font_size_text',
        'font_size_table',
        'header_font_size',
        'footer_font_size',
        'font_style_title',
        'font_style_text',
        'header_font_style',
        'footer_font_style',
        'header_image_vertical_position',
        'footer_image_vertical_position',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_table_lines' => 'boolean',
        'use_zebra_stripes' => 'boolean',
        'real_time_preview' => 'boolean',
        'margin_top' => 'float',
        'margin_bottom' => 'float',
        'margin_left' => 'float',
        'margin_right' => 'float',
    ];
}
