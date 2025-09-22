<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Models\{VehicleCategory, FuelType, Secretariat, Department, VehicleStatus};

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = VehicleCategory::pluck('id','name');
        $fuelTypes = FuelType::pluck('id','name');
        $statusDisponivel = VehicleStatus::where('slug','disponivel')->first()?->id;

        $secretariat = Secretariat::first();
        $department = Department::where('secretariat_id',$secretariat?->id)->first();

        $vehicles = [
            [
                'brand'=>'Toyota','model'=>'Corolla','year'=>2022,'plate'=>'ABC1A23','renavam'=>Str::random(11),'prefix'=>'VEH-001','current_km'=>35000,
                'fuel_type_id'=>$fuelTypes['Gasolina'] ?? null,'vehicle_category_id'=>$categories['Automóvel Leve'] ?? null,
                'current_secretariat_id'=>$secretariat?->id,'current_department_id'=>$department?->id,'tank_capacity'=>55,'avg_km_per_liter'=>12.5,
                'status'=>'Disponível','vehicle_status_id'=>$statusDisponivel
            ],
            [
                'brand'=>'Ford','model'=>'Ranger','year'=>2021,'plate'=>'DEF2B34','renavam'=>Str::random(11),'prefix'=>'VEH-002','current_km'=>58000,
                'fuel_type_id'=>$fuelTypes['Diesel'] ?? null,'vehicle_category_id'=>$categories['Utilitário'] ?? null,
                'current_secretariat_id'=>$secretariat?->id,'current_department_id'=>$department?->id,'tank_capacity'=>80,'avg_km_per_liter'=>9.3,
                'status'=>'Disponível','vehicle_status_id'=>$statusDisponivel
            ],
            [
                'brand'=>'VW','model'=>'Delivery','year'=>2020,'plate'=>'GHI3C45','renavam'=>Str::random(11),'prefix'=>'VEH-003','current_km'=>120000,
                'fuel_type_id'=>$fuelTypes['Diesel'] ?? null,'vehicle_category_id'=>$categories['Caminhão'] ?? null,
                'current_secretariat_id'=>$secretariat?->id,'current_department_id'=>$department?->id,'tank_capacity'=>130,'avg_km_per_liter'=>6.1,
                'status'=>'Disponível','vehicle_status_id'=>$statusDisponivel
            ],
            [
                'brand'=>'Marcopolo','model'=>'Torino','year'=>2019,'plate'=>'JKL4D56','renavam'=>Str::random(11),'prefix'=>'VEH-004','current_km'=>300000,
                'fuel_type_id'=>$fuelTypes['Diesel'] ?? null,'vehicle_category_id'=>$categories['Ônibus'] ?? null,
                'current_secretariat_id'=>$secretariat?->id,'current_department_id'=>$department?->id,'tank_capacity'=>300,'avg_km_per_liter'=>2.8,
                'status'=>'Disponível','vehicle_status_id'=>$statusDisponivel
            ],
        ];

        $tableCols = Schema::getColumnListing('vehicles');

        foreach ($vehicles as $v) {
            if (!DB::table('vehicles')->where('plate',$v['plate'])->exists()) {
                // Filtra apenas colunas existentes para robustez
                $record = collect($v)->filter(function($val,$key) use ($tableCols){ return in_array($key,$tableCols); })->all();
                $record['created_at'] = now();
                $record['updated_at'] = now();
                DB::table('vehicles')->insert($record);
            }
        }
    }
}
