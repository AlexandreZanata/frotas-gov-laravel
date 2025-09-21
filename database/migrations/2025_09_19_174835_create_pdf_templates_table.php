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
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('header_image')->nullable();
            $table->string('footer_image')->nullable();
            $table->integer('margin_top')->default(10);
            $table->integer('margin_bottom')->default(10);
            $table->integer('margin_left')->default(10);
            $table->integer('margin_right')->default(10);
            $table->string('font_family')->default('helvetica');
            $table->integer('font_size_title')->default(16);
            $table->integer('font_size_text')->default(12);
            $table->integer('font_size_table')->default(10);
            $table->string('font_style_title')->default('B'); // B for Bold
            $table->string('font_style_text')->default(''); // Empty for Regular
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_templates');
    }
};
