<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Vehicle;

return new class extends Migration
{
    /**
     * Run the migrations.
     */


    public function up(): void
    {
        // Vamos definir a categoria 'Veículo Leve' (ID=1) como padrão para todos os veículos existentes.
        // Isso evita que a coluna fique nula.
        // Você pode ajustar o ID se sua categoria padrão for outra.
        Vehicle::whereNull('vehicle_category_id')->update(['vehicle_category_id' => 1]);
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
