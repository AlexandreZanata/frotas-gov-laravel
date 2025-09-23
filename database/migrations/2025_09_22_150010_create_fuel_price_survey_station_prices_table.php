<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_price_survey_station_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_price_survey_id')->constrained('fuel_price_surveys')->cascadeOnDelete();
            $table->foreignId('gas_station_id')->constrained('gas_stations');
            $table->boolean('include_in_average')->default(true);
            $table->boolean('include_in_comparison')->default(true);
            $table->decimal('diesel_s500_price',8,3)->nullable();
            $table->decimal('diesel_s10_price',8,3)->nullable();
            $table->decimal('gasoline_price',8,3)->nullable();
            $table->decimal('ethanol_price',8,3)->nullable();
            // Comprovantes (um por tipo de combustível)
            $table->string('diesel_s500_attachment_path')->nullable();
            $table->string('diesel_s10_attachment_path')->nullable();
            $table->string('gasoline_attachment_path')->nullable();
            $table->string('ethanol_attachment_path')->nullable();
            $table->timestamps();
            $table->unique(['fuel_price_survey_id','gas_station_id'],'survey_station_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_price_survey_station_prices');
    }
};

