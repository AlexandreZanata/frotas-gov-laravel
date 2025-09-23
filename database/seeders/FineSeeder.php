<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Fine, FineInfraction, Vehicle, User};
use Illuminate\Support\Str;

class FineSeeder extends Seeder
{
    public function run(): void
    {
        $vehicle = Vehicle::query()->inRandomOrder()->first();
        $driver = User::query()->where('role_id',4)->inRandomOrder()->first();
        if (!$vehicle || !$driver) {
            $this->command?->warn('FineSeeder: sem veículo ou motorista, pulando.');
            return;
        }

        $samples = [
            ['AUTO-TEST-001','draft', ['A01'=>[100,10,0,0,0],'B15'=>[80,0,0,0,0]]],
            ['AUTO-TEST-002','aguardando_pagamento', ['C10'=>[150,0,5,0,10]]],
            ['AUTO-TEST-003','pago', ['D22'=>[200,0,0,20,0],'E05'=>[50,0,0,0,0]]],
            ['AUTO-TEST-004','cancelado', ['F30'=>[120,0,0,0,0]]],
        ];

        foreach ($samples as [$auto,$status,$infractions]) {
            $fine = Fine::create([
                'auto_number'=>$auto,
                'vehicle_id'=>$vehicle->id,
                'driver_id'=>$driver->id,
                'status'=>$status,
                'notes'=>'Multa de exemplo seed'
            ]);
            foreach ($infractions as $code=>$vals) {
                [$base,$extraFixed,$extraPercent,$discountFixed,$discountPercent] = $vals;
                FineInfraction::create([
                    'fine_id'=>$fine->id,
                    'code'=>$code,
                    'description'=>'Infração '.$code,
                    'base_amount'=>$base,
                    'extra_fixed'=>$extraFixed,
                    'extra_percent'=>$extraPercent,
                    'discount_fixed'=>$discountFixed,
                    'discount_percent'=>$discountPercent,
                ]);
            }
            // Ajusta timestamps específicos
            if ($status === 'pago') {
                $fine->paid_at = now();
                $fine->saveQuietly();
            }
        }
    }
}

