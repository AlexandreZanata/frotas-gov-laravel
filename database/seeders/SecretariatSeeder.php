<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Secretariat;

class SecretariatSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Administração Geral',
            'Transporte Escolar',
            'Saúde',
            'Obras e Infraestrutura',
            'Segurança'
        ];
        foreach ($items as $name) {
            Secretariat::firstOrCreate(['name'=>$name]);
        }
    }
}

