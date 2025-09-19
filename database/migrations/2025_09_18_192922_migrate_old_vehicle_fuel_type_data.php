<?php

// database/migrations/xxxx_xx_xx_xxxxxx_migrate_old_vehicle_fuel_type_data.php
use App\Models\FuelType;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $fuelTypeMap = FuelType::all()->pluck('id', 'name');
        $defaultFuelTypeId = $fuelTypeMap->get('Gasolina'); // Ou outro padrão que faça sentido

        // A CORREÇÃO ESTÁ AQUI: trocamos whereNotNull('fuel_type') por all()
        // Isso garante que TODOS os veículos sejam processados.
        Vehicle::all()->each(function ($vehicle) use ($fuelTypeMap,  $defaultFuelTypeId) {
            // Se o fuel_type for nulo, o .get() vai usar o valor padrão.
            $vehicle->fuel_type_id = $fuelTypeMap->get($vehicle->fuel_type, $defaultFuelTypeId);
            $vehicle->save();
        });
    }

    public function down(): void
    {
        // ...
    }
};
