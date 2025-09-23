<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FuelType;

class FuelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Gasolina','Etanol','Diesel','GNV','Flex','Elétrico'];
        foreach ($types as $name) {
            FuelType::firstOrCreate(['name'=>$name]);
        }
    }
}
