<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\{Run, Vehicle, FuelType, GasStation, Fueling};

class FuelingSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('fuelings') || !Schema::hasTable('runs')) return;
        if (Fueling::query()->exists()) return; // não duplicar

        // Garante postos
        if (!GasStation::count()) {
            (new GasStationSeeder())->run();
        }
        $stations = GasStation::inRandomOrder()->get();
        $fuelTypes = FuelType::pluck('id','name');

        $runs = Run::with('vehicle')->limit(15)->get();
        if ($runs->isEmpty()) {
            // Se não há corridas criadas, não gera abastecimentos (evita criar dados artificiais demais)
            return;
        }

        $rows = [];
        foreach ($runs as $run) {
            if (!$run->vehicle) continue;
            $station = $stations->random();
            $liters = rand(10,60);
            $price = rand(450,650)/100; // 4.50 a 6.50
            $rows[] = [
                'run_id' => $run->id,
                'user_id' => $run->driver_id,
                'vehicle_id' => $run->vehicle_id,
                'secretariat_id' => $run->secretariat_id,
                'gas_station_id' => $station->id,
                'fuel_type_id' => $fuelTypes['Diesel'] ?? $fuelTypes->first(),
                'gas_station_name' => null,
                'km' => $run->start_km ?? rand(1000,5000),
                'liters' => $liters,
                'total_value' => round($liters * $price,2),
                'invoice_path' => null,
                'is_manual' => false,
                'created_at' => now()->subDays(rand(0,30))
            ];
        }
        if ($rows) {
            DB::table('fuelings')->insert($rows);
        }
    }
}

