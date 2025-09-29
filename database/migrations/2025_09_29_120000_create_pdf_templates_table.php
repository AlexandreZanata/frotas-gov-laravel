<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Header
            $table->string('header_image')->nullable();
            $table->string('footer_image')->nullable();
            $table->string('header_scope')->default('all'); // all, first, none
            $table->char('header_image_align', 1)->default('L');
            $table->unsignedInteger('header_image_width')->nullable();
            $table->unsignedInteger('header_image_height')->nullable();
            $table->text('header_text')->nullable();
            $table->char('header_text_align', 1)->default('R');
            $table->float('header_line_height', 8, 2)->default(1.2); // NOVO

            // Footer
            $table->string('footer_scope')->default('all');
            $table->char('footer_image_align', 1)->default('L');
            $table->unsignedInteger('footer_image_width')->nullable();
            $table->unsignedInteger('footer_image_height')->nullable();
            $table->text('footer_text')->nullable();
            $table->char('footer_text_align', 1)->default('C');
            $table->float('footer_line_height', 8, 2)->default(1.2); // NOVO

            // Body
            $table->text('body_text')->nullable();
            $table->text('after_table_text')->nullable();

            // Table
            $table->string('table_style')->nullable();
            $table->string('table_header_bg')->default('#E5E7EB');
            $table->string('table_header_text')->default('#1F2937');
            $table->unsignedTinyInteger('table_row_height')->default(7);
            $table->boolean('show_table_lines')->default(true);
            $table->boolean('use_zebra_stripes')->default(true);
            $table->text('table_columns')->nullable();

            // General & Preview
            $table->boolean('real_time_preview')->default(true);
            $table->float('margin_top')->default(25);
            $table->float('margin_bottom')->default(25);
            $table->float('margin_left')->default(15);
            $table->float('margin_right')->default(15);

            // Fonts
            $table->string('font_family')->default('helvetica');
            $table->string('font_family_body')->default('helvetica');
            $table->string('header_font_family')->default('helvetica');
            $table->string('footer_font_family')->default('helvetica');
            $table->unsignedTinyInteger('font_size_title')->default(14);
            $table->unsignedTinyInteger('font_size_text')->default(11);
            $table->unsignedTinyInteger('font_size_table')->default(9);
            $table->unsignedTinyInteger('header_font_size')->default(10);
            $table->unsignedTinyInteger('footer_font_size')->default(8);
            $table->string('font_style_title', 10)->nullable()->default('B');
            $table->string('font_style_text', 10)->nullable();
            $table->string('header_font_style', 10)->nullable();
            $table->string('footer_font_style', 10)->nullable()->default('I');

            // Positioning
            $table->string('header_image_vertical_position')->default('inline-left');
            $table->string('footer_image_vertical_position')->default('inline-left');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_templates');
    }
};

