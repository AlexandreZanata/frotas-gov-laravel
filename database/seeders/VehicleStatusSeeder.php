<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FuelType;

class FuelTypeSeeder extends Seeder
{
    public function run(): void
    {
        FuelType::truncate(); // Limpa a tabela antes de popular

        $types = ['Gasolina', 'Etanol', 'Diesel', 'GNV', 'Flex', 'Elétrico'];

        foreach ($types as $type) {
            FuelType::create(['name' => $type]);
        }
    }
}
