<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Role, Vehicle, Fine, FineInfraction, Secretariat, Department, VehicleCategory, FuelType, VehicleStatus};
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/**
 * Helpers
 */
function createRoles(): void {
    foreach ([1=>'general_manager',2=>'sector_manager',3=>'mechanic',4=>'driver'] as $id=>$name) {
        Role::forceCreate(['id'=>$id,'name'=>$name]);
    }
}
function baseRefs(): array {
    $secretariat = Secretariat::create(['name'=>'Sec Test']);
    $department = Department::create(['name'=>'Dep Test','secretariat_id'=>$secretariat->id]);
    $cat = VehicleCategory::create(['name'=>'Cat','oil_change_km'=>10000,'oil_change_days'=>180,'tire_change_km'=>50000,'tire_change_days'=>365]);
    $fuel = FuelType::create(['name'=>'Gas']);
    $status = VehicleStatus::create(['name'=>'Disponível','slug'=>'disponivel','color'=>'success']);
    return compact('secretariat','department','cat','fuel','status');
}
function createVehicle(): Vehicle {
    $r = baseRefs();
    return Vehicle::create([
        'brand'=>'FORD','model'=>'RANGER','year'=>2024,'plate'=>'ABC1A23','renavam'=>'12345678901','chassi'=>'CHASSI1234567890','prefix'=>'PX1','current_km'=>1000,
        'fuel_type_id'=>$r['fuel']->id,'document_path'=>null,'category_id'=>null,'vehicle_category_id'=>$r['cat']->id,
        'current_secretariat_id'=>$r['secretariat']->id,'current_department_id'=>$r['department']->id,'tank_capacity'=>80,'avg_km_per_liter'=>10,
        'vehicle_status_id'=>$r['status']->id
    ]);
}
function createManager(): User { return User::create(['name'=>'Manager','email'=>'manager@test.com','password'=>Hash::make('pass'),'role_id'=>2]); }
function createDriver(): User { return User::create(['name'=>'Driver','email'=>'driver@test.com','password'=>Hash::make('pass'),'role_id'=>4]); }

it('cria multa com infrações e calcula total automaticamente', function(){
    createRoles(); $manager = createManager(); $vehicle = createVehicle(); $this->actingAs($manager);
    $fine = Fine::create(['auto_number'=>'AUTO-1','vehicle_id'=>$vehicle->id,'driver_id'=>null,'notes'=>'Teste']);
    expect($fine->total_amount)->toBe(0.0);
    FineInfraction::create(['fine_id'=>$fine->id,'code'=>'X1','description'=>'Infração 1','base_amount'=>100,'extra_fixed'=>0,'extra_percent'=>10,'discount_fixed'=>0,'discount_percent'=>0]);
    $fine->refresh(); expect($fine->total_amount)->toBe(110.0);
    FineInfraction::create(['fine_id'=>$fine->id,'code'=>'X2','description'=>'Infração 2','base_amount'=>50,'extra_fixed'=>5,'extra_percent'=>0,'discount_fixed'=>0,'discount_percent'=>0]);
    $fine->refresh(); expect($fine->total_amount)->toBe(165.0);
});

it('bloqueia driver com multa aguardando ciência redirecionando para pendentes', function(){
    createRoles(); $driver = createDriver(); $vehicle = createVehicle();
    $fine = Fine::create(['auto_number'=>'AUTO-2','vehicle_id'=>$vehicle->id,'driver_id'=>$driver->id,'status'=>'aguardando_pagamento']);
    $this->actingAs($driver);
    $resp = $this->get('/vehicles');
    $resp->assertRedirect(route('fines.pending'));
});

it('permite registrar ciência e depois acessar rotas sem bloqueio', function(){
    createRoles(); $driver = createDriver(); $vehicle = createVehicle();
    $fine = Fine::create(['auto_number'=>'AUTO-3','vehicle_id'=>$vehicle->id,'driver_id'=>$driver->id,'status'=>'aguardando_pagamento']);
    $this->actingAs($driver);
    $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    $this->post(route('fines.ack',$fine))->assertRedirect(route('dashboard'));
    $fine->refresh(); expect($fine->acknowledged_at)->not->toBeNull();
    $this->get('/dashboard')->assertOk();
});

it('registra histórico ao mudar status', function(){
    createRoles(); $manager = createManager(); $vehicle = createVehicle(); $this->actingAs($manager);
    $fine = Fine::create(['auto_number'=>'AUTO-4','vehicle_id'=>$vehicle->id,'status'=>'draft']);
    $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    $this->post(route('fines.change-status',$fine), ['status'=>'aguardando_pagamento'])->assertRedirect();
    $fine->refresh(); expect($fine->status)->toBe('aguardando_pagamento'); expect($fine->statusHistories()->count())->toBe(1);
});

it('gera PDF da multa', function(){
    createRoles(); $manager = createManager(); $vehicle = createVehicle(); $this->actingAs($manager);
    $fine = Fine::create(['auto_number'=>'AUTO-5','vehicle_id'=>$vehicle->id,'status'=>'draft']);
    FineInfraction::create(['fine_id'=>$fine->id,'code'=>'PDF','description'=>'Teste PDF','base_amount'=>10]);
    $resp = $this->get(route('fines.pdf',$fine)); $resp->assertOk();
});
