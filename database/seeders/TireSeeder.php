<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Vehicle, Tire};

class TireSeeder extends Seeder
{
    public function run(): void
    {
        if (Tire::count() > 0) return; // mantém idempotência básica

        $vehicles = Vehicle::with('category')->limit(4)->get();
        $dimensionMap = [
            'Automóvel Leve' => '195/55 R16',
            'Utilitário' => '255/70 R16',
            'Caminhão' => '275/80 R22.5',
            'Ônibus' => '295/80 R22.5'
        ];

        $serialIndex = 1;
        $makeSerial = function() use (&$serialIndex) { return 'PN-'.str_pad($serialIndex++,5,'0',STR_PAD_LEFT); };

        // Instala 4 pneus básicos por veículo (ou layout simples) como in_use
        foreach ($vehicles as $vehicle) {
            $categoryName = $vehicle->category?->name;
            $dim = $dimensionMap[$categoryName] ?? '195/55 R16';
            foreach (['FL','FR','RL','RR'] as $pos) {
                Tire::create([
                    'serial_number' => $makeSerial(),
                    'brand' => 'Pirelli',
                    'model' => 'PZero',
                    'dimension' => $dim,
                    'purchase_date' => now()->subMonths(rand(2,18))->toDateString(),
                    'initial_tread_depth_mm' => 9.0,
                    'current_tread_depth_mm' => 9.0 - rand(5,40)/10,
                    'expected_tread_life_km' => 60000,
                    'accumulated_km' => rand(5000,50000),
                    'status' => 'in_use',
                    'current_vehicle_id' => $vehicle->id,
                    'position' => $pos,
                    'installed_at' => now()->subMonths(rand(1,6)),
                    'notes' => 'Pneu instalado em '.$vehicle->plate,
                ]);
            }
        }

        // Pneus em estoque (stock)
        for ($i=0;$i<8;$i++) {
            Tire::create([
                'serial_number' => $makeSerial(),
                'brand' => 'Michelin',
                'model' => 'EnergySaver',
                'dimension' => '195/55 R16',
                'purchase_date' => now()->subMonths(rand(1,12))->toDateString(),
                'initial_tread_depth_mm' => 9.0,
                'current_tread_depth_mm' => 9.0,
                'expected_tread_life_km' => 60000,
                'accumulated_km' => 0,
                'status' => 'stock',
                'notes' => 'Estoque geral',
            ]);
        }

        // Pneus em atenção (attention) – sulco mais gasto / km alto
        for ($i=0;$i<3;$i++) {
            Tire::create([
                'serial_number' => $makeSerial(),
                'brand' => 'Goodyear',
                'model' => 'EfficientGrip',
                'dimension' => '255/70 R16',
                'purchase_date' => now()->subMonths(rand(8,20))->toDateString(),
                'initial_tread_depth_mm' => 10.0,
                'current_tread_depth_mm' => 3.5,
                'expected_tread_life_km' => 70000,
                'accumulated_km' => 52000,
                'status' => 'attention',
                'notes' => 'Aproximando-se do limite de desgaste',
            ]);
        }

        // Pneus críticos (critical)
        for ($i=0;$i<2;$i++) {
            Tire::create([
                'serial_number' => $makeSerial(),
                'brand' => 'Bridgestone',
                'model' => 'Duravis',
                'dimension' => '275/80 R22.5',
                'purchase_date' => now()->subMonths(rand(10,24))->toDateString(),
                'initial_tread_depth_mm' => 11.0,
                'current_tread_depth_mm' => 2.0,
                'expected_tread_life_km' => 80000,
                'accumulated_km' => 76000,
                'status' => 'critical',
                'notes' => 'Abaixo do limite recomendado – substituir',
            ]);
        }

        // Pneu em recapagem (recap_out)
        Tire::create([
            'serial_number' => $makeSerial(),
            'brand' => 'Firestone',
            'model' => 'FS400',
            'dimension' => '295/80 R22.5',
            'purchase_date' => now()->subYear()->toDateString(),
            'initial_tread_depth_mm' => 11.0,
            'current_tread_depth_mm' => 1.5,
            'expected_tread_life_km' => 90000,
            'accumulated_km' => 85000,
            'status' => 'recap_out',
            'notes' => 'Enviado para recapagem em '.now()->subDays(10)->toDateString(),
        ]);
    }
}
