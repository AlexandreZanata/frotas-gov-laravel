<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleStatus;

class VehicleStatusSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name'=>'Disponível','slug'=>'disponivel','color'=>'green'],
            ['name'=>'Em uso','slug'=>'em-uso','color'=>'blue'],
            ['name'=>'Manutenção','slug'=>'manutencao','color'=>'yellow'],
            ['name'=>'Inativo','slug'=>'inativo','color'=>'gray'],
        ];
        foreach ($data as $row) {
            VehicleStatus::updateOrCreate(['slug'=>$row['slug']], $row);
        }
    }
}
