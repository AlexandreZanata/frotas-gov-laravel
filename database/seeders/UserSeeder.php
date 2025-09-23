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
        $now = now();
        $secretariatId = DB::table('secretariats')->value('id');
        if (!$secretariatId) {
            $secretariatId = DB::table('secretariats')->insertGetId([
                'name' => 'Administração Geral',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        // Pega qualquer departamento vinculado ou cria um genérico
        $departmentId = DB::table('departments')->where('secretariat_id',$secretariatId)->value('id');
        if (!$departmentId) {
            $departmentId = DB::table('departments')->insertGetId([
                'secretariat_id'=>$secretariatId,
                'name'=>'Departamento Geral',
                'created_at'=>$now,
                'updated_at'=>$now
            ]);
        }

        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@frotas.gov',
                'role_id' => 1,
                'cpf' => '00000000000',
            ],
            [
                'name' => 'Gestor Setorial',
                'email' => 'gestor@frotas.gov',
                'role_id' => 2,
                'cpf' => '11111111111',
            ],
            [
                'name' => 'Mecanico',
                'email' => 'mecanico@frotas.gov',
                'role_id' => 3,
                'cpf' => '22222222222',
            ],
            [
                'name' => 'Motorista',
                'email' => 'motorista@frotas.gov',
                'role_id' => 4,
                'cpf' => '33333333333',
            ],
        ];

        foreach ($users as $u) {
            if (!DB::table('users')->where('email',$u['email'])->exists()) {
                DB::table('users')->insert([
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => Hash::make('senha123'),
                    'cpf' => $u['cpf'],
                    'role_id' => $u['role_id'],
                    'secretariat_id' => $secretariatId,
                    'department_id' => $departmentId,
                    'status' => 'active',
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
