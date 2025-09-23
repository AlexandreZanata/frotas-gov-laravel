<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $existing = DB::table('vehicle_statuses')->pluck('slug')->toArray();
        $rows = [];
        if (!in_array('disponivel',$existing)) {
            $rows[] = ['name'=>'Disponível','slug'=>'disponivel','color'=>'green','created_at'=>$now,'updated_at'=>$now];
        }
        if (!in_array('bloqueado',$existing)) {
            $rows[] = ['name'=>'Bloqueado','slug'=>'bloqueado','color'=>'red','created_at'=>$now,'updated_at'=>$now];
        }
        if ($rows) {
            DB::table('vehicle_statuses')->insert($rows);
        }
    }

    public function down(): void
    {
        // Não remove para não perder histórico
    }
};

