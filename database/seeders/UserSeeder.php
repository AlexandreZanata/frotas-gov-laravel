<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crie primeiro uma secretaria padrão para associar o admin
        $secretariatId = DB::table('secretariats')->insertGetId([
            'name' => 'Administração Geral',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Seu Nome Completo', // <-- MUDE AQUI
            'email' => 'admin@frotas.gov', // <-- MUDE AQUI
            'password' => Hash::make('senha123'), // <-- MUDE AQUI
            'cpf' => '00000000000',
            'role_id' => 1, // 1 = Gestor Geral
            'secretariat_id' => $secretariatId,
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
