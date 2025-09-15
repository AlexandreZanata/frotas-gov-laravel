<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'general_manager', 'description' => 'Gestor Geral'],
            ['id' => 2, 'name' => 'sector_manager', 'description' => 'Gestor Setorial'],
            ['id' => 3, 'name' => 'mechanic', 'description' => 'Mecânico'],
            ['id' => 4, 'name' => 'driver', 'description' => 'Motorista'],
        ]);
    }
}
