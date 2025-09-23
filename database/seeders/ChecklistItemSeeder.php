<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name'=>'Pneus calibrados','description'=>'Verificar pressão de todos os pneus'],
            ['name'=>'Nível de óleo','description'=>'Conferir nível e possíveis vazamentos'],
            ['name'=>'Freios','description'=>'Checar funcionamento e ruídos'],
            ['name'=>'Iluminação','description'=>'Faróis, lanternas, setas, luz de freio'],
            ['name'=>'Cinto de segurança','description'=>'Integridade e funcionamento'],
        ];
        foreach ($items as $i) {
            if (!DB::table('checklist_items')->where('name',$i['name'])->exists()) {
                DB::table('checklist_items')->insert($i);
            }
        }
    }
}

