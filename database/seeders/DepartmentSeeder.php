<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Secretariat, Department};

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            'Administração Geral' => ['Financeiro','RH','TI'],
            'Transporte Escolar' => ['Rota Norte','Rota Sul'],
            'Saúde' => ['Ambulatório','Vigilância'],
            'Obras e Infraestrutura' => ['Pavimentação','Iluminação'],
            'Segurança' => ['Patrulha 1','Patrulha 2']
        ];
        foreach ($mapping as $secretariatName => $departments) {
            $sec = Secretariat::firstWhere('name',$secretariatName);
            if (!$sec) continue;
            foreach ($departments as $depName) {
                Department::firstOrCreate(['secretariat_id'=>$sec->id,'name'=>$depName]);
            }
        }
    }
}

