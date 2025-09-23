<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GasStationSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('gas_stations')) return;
        $stations = [
            'Posto Central','Posto Avenida','Auto Posto Brasil','Posto Diesel Max','Posto Etanol Verde'
        ];
        $existing = DB::table('gas_stations')->pluck('name')->toArray();
        $insert = [];
        foreach ($stations as $name) {
            if (!in_array($name,$existing)) {
                $insert[] = ['name'=>$name,'status'=>'active'];
            }
        }
        if ($insert) { DB::table('gas_stations')->insert($insert); }
    }
}

