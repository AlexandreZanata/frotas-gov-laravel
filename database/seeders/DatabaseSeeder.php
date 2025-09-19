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
        // Adicione estas duas linhas:
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            VehicleStatusSeeder::class,
            ChecklistItemSeeder::class,

        ]);
    }
}
