<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OilProduct;

class OilProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Óleo Sintético Premium 5W30',
                'code' => 'OL-5W30-PREM',
                'brand' => 'Castrol',
                'viscosity' => '5W30',
                'stock_quantity' => 40,
                'reorder_level' => 10,
                'unit_cost' => 55.90,
                'recommended_interval_km' => 10000,
                'recommended_interval_days' => 180,
                'description' => 'Óleo sintético de alta performance para motores modernos.'
            ],
            [
                'name' => 'Óleo Semi-Sintético 10W40',
                'code' => 'OL-10W40-SEMI',
                'brand' => 'Shell',
                'viscosity' => '10W40',
                'stock_quantity' => 60,
                'reorder_level' => 15,
                'unit_cost' => 38.50,
                'recommended_interval_km' => 8000,
                'recommended_interval_days' => 150,
                'description' => 'Equilíbrio entre proteção e custo para motores de uso geral.'
            ],
            [
                'name' => 'Óleo Mineral 15W40',
                'code' => 'OL-15W40-MIN',
                'brand' => 'Petrobras',
                'viscosity' => '15W40',
                'stock_quantity' => 80,
                'reorder_level' => 20,
                'unit_cost' => 25.00,
                'recommended_interval_km' => 6000,
                'recommended_interval_days' => 120,
                'description' => 'Óleo mineral indicado para motores mais antigos.'
            ],
            [
                'name' => 'Óleo Sintético Long Life 0W20',
                'code' => 'OL-0W20-LL',
                'brand' => 'Mobil',
                'viscosity' => '0W20',
                'stock_quantity' => 25,
                'reorder_level' => 8,
                'unit_cost' => 72.00,
                'recommended_interval_km' => 12000,
                'recommended_interval_days' => 210,
                'description' => 'Baixa viscosidade para máxima eficiência e economia de combustível.'
            ],
        ];

        foreach ($items as $data) {
            OilProduct::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}

