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
        Schema::create('oil_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('Código interno / SKU');
            $table->string('brand')->nullable();
            $table->string('viscosity')->nullable()->comment('Ex: 5W30');
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('reorder_level')->default(0)->comment('Nível de estoque para alerta');
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->unsignedInteger('recommended_interval_km')->default(5000);
            $table->unsignedInteger('recommended_interval_days')->default(180);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oil_products');
    }
};
