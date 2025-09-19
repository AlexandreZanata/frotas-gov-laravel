<?php

use App\Models\Vehicle;
use App\Models\VehicleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// A ESTRUTURA QUE ESTAVA FALTANDO É ESTA LINHA E OS '}' NO FINAL
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pega os status do banco e cria um mapa 'Nome' => id
        // É importante rodar "php artisan db:seed" ANTES de "php artisan migrate"
        // para que esta migration encontre os status.
        $statusMap = VehicleStatus::all()->pluck('id', 'name');

        // Se por algum motivo o seeder não rodou, usa um valor padrão seguro
        $defaultStatusId = $statusMap->get('Inativo', 4);

        // Atualiza cada veículo
        Vehicle::all()->each(function ($vehicle) use ($statusMap, $defaultStatusId) {
            if ($vehicle->status) { // Garante que não tente migrar um status vazio
                $vehicle->vehicle_status_id = $statusMap->get($vehicle->status, $defaultStatusId);
                $vehicle->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Esta migration não precisa reverter dados, pois a próxima reverterá a estrutura.
    }
}; // <-- ESTE '};' TAMBÉM FAZ PARTE DA ESTRUTURA
