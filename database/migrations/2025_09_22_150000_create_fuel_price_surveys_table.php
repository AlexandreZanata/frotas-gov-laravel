<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_price_surveys', function (Blueprint $table) {
            $table->id();
            $table->date('survey_date');
            $table->enum('method', ['simple','custom'])->default('simple');
            // Percentuais de desconto aplicados sobre a média (em %)
            $table->decimal('discount_diesel_s500',5,2)->nullable();
            $table->decimal('discount_diesel_s10',5,2)->nullable();
            $table->decimal('discount_gasoline',5,2)->nullable();
            $table->decimal('discount_ethanol',5,2)->nullable();
            // Valores de média customizados (opcional quando method = custom)
            $table->decimal('custom_avg_diesel_s500',8,3)->nullable();
            $table->decimal('custom_avg_diesel_s10',8,3)->nullable();
            $table->decimal('custom_avg_gasoline',8,3)->nullable();
            $table->decimal('custom_avg_ethanol',8,3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_price_surveys');
    }
};

