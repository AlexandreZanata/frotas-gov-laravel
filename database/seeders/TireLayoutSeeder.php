<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TireLayout;

class TireLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'name'=>'Carro 4 Posições Básico',
                'code'=>'car_4_basic',
                'description'=>'Layout frontal/traseiro simples para veículos leves (4 pneus).',
                'positions'=>[
                    ['code'=>'FL','label'=>'Dianteiro Esquerdo','x'=>25,'y'=>30],
                    ['code'=>'FR','label'=>'Dianteiro Direito','x'=>75,'y'=>30],
                    ['code'=>'RL','label'=>'Traseiro Esquerdo','x'=>25,'y'=>75],
                    ['code'=>'RR','label'=>'Traseiro Direito','x'=>75,'y'=>75],
                ]
            ],
            [
                'name'=>'Caminhão 6x2 (Eixo Duplo Traseiro)',
                'code'=>'truck_6x2',
                'description'=>'Configuração comum com eixo duplo traseiro simples.',
                'positions'=>[
                    ['code'=>'FL','label'=>'Dianteiro Esquerdo','x'=>25,'y'=>20],
                    ['code'=>'FR','label'=>'Dianteiro Direito','x'=>75,'y'=>20],
                    ['code'=>'ML','label'=>'Meio Esquerdo','x'=>30,'y'=>55],
                    ['code'=>'MR','label'=>'Meio Direito','x'=>70,'y'=>55],
                    ['code'=>'RL','label'=>'Traseiro Esquerdo','x'=>30,'y'=>85],
                    ['code'=>'RR','label'=>'Traseiro Direito','x'=>70,'y'=>85],
                ]
            ],
            [
                'name'=>'Ônibus Urbano 6 Posições',
                'code'=>'bus_6',
                'description'=>'Layout simplificado para ônibus de 6 pneus (eixo duplo traseiro).',
                'positions'=>[
                    ['code'=>'FL','label'=>'Dianteiro Esquerdo','x'=>25,'y'=>25],
                    ['code'=>'FR','label'=>'Dianteiro Direito','x'=>75,'y'=>25],
                    ['code'=>'ML','label'=>'Meio Esquerdo','x'=>30,'y'=>55],
                    ['code'=>'MR','label'=>'Meio Direito','x'=>70,'y'=>55],
                    ['code'=>'RL','label'=>'Traseiro Esquerdo','x'=>30,'y'=>85],
                    ['code'=>'RR','label'=>'Traseiro Direito','x'=>70,'y'=>85],
                ]
            ]
        ];

        foreach ($defaults as $d) {
            TireLayout::updateOrCreate(
                ['code'=>$d['code']],
                [
                    'name'=>$d['name'],
                    'description'=>$d['description'],
                    'positions'=>$d['positions']
                ]
            );
        }
    }
}

