<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{VehicleCategory, TireLayout};

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $layoutMap = [
            'Automóvel Leve' => 'car_4_basic',
            'Utilitário' => 'car_4_basic',
            'Caminhão' => 'truck_6x2',
            'Ônibus' => 'bus_6',
        ];
        $items = [
            ['name'=>'Automóvel Leve','layout_key'=>'car_2x2','oil_change_km'=>10000,'oil_change_days'=>180],
            ['name'=>'Utilitário','layout_key'=>'pickup_2x2','oil_change_km'=>8000,'oil_change_days'=>150],
            ['name'=>'Caminhão','layout_key'=>'truck_2x4','oil_change_km'=>15000,'oil_change_days'=>200],
            ['name'=>'Ônibus','layout_key'=>'bus_2x4','oil_change_km'=>20000,'oil_change_days'=>240],
        ];
        foreach ($items as $row) {
            $layoutCode = $layoutMap[$row['name']] ?? null;
            $layoutId = $layoutCode ? TireLayout::where('code',$layoutCode)->value('id') : null;
            $payload = $row + ['tire_layout_id'=>$layoutId];
            VehicleCategory::updateOrCreate(['name'=>$row['name']], $payload);
        }
    }
}
