<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SecretariatSeeder::class,
            DepartmentSeeder::class,
            VehicleStatusSeeder::class,
            FuelTypeSeeder::class,
            TireLayoutSeeder::class, // layouts antes das categorias para poder vincular
            VehicleCategorySeeder::class,
            ChecklistItemSeeder::class,
            UserSeeder::class,
            VehicleSeeder::class,
            GasStationSeeder::class, // novos postos antes de abastecimentos
            TireSeeder::class,
            OilProductSeeder::class,
            FineSeeder::class, // novo seeder de multas
            FuelingSeeder::class, // abastecimentos de teste (idempotente)
        ]);
    }
}
