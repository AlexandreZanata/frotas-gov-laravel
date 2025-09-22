<?php

use App\Models\{User, Role, Vehicle, Tire};

beforeEach(function() {
    // Garante que exista uma Role com id=1 para permissões (Gestor)
    $role = Role::query()->where('id',1)->first();
    if (!$role) {
        $role = Role::create(['id'=>1,'name'=>'gestor-geral','description'=>'Gestor Geral']);
    }
    $this->user = User::factory()->create(['role_id'=>$role->id]);
});

it('exibe a lista de pneus vazia', function() {
    $response = $this->actingAs($this->user)->get(route('tires.index'));
    $response->assertStatus(200)->assertSee('Pneus');
});

it('cria um pneu', function() {
    $payload = [
        'serial_number' => 'PN-TEST-001',
        'brand' => 'Teste',
        'model' => 'A1',
        'dimension' => '195/55 R16',
        'purchase_date' => '2025-01-01',
        'initial_tread_depth_mm' => 8.5,
        'current_tread_depth_mm' => 8.5,
        'expected_tread_life_km' => 50000,
        'notes' => 'Pneu de teste'
    ];
    $response = $this->actingAs($this->user)->post(route('tires.store'), $payload);
    $response->assertRedirect(route('tires.index'));
    $this->assertDatabaseHas('tires', ['serial_number'=>'PN-TEST-001']);
});

it('realiza rodizio interno entre FL e FR', function() {
    $vehicle = Vehicle::factory()->create();
    $tireA = Tire::factory()->create(['serial_number'=>'PN-A-001','current_vehicle_id'=>$vehicle->id,'position'=>'FL','status'=>'in_use']);
    $tireB = Tire::factory()->create(['serial_number'=>'PN-B-001','current_vehicle_id'=>$vehicle->id,'position'=>'FR','status'=>'in_use']);

    $response = $this->actingAs($this->user)->post(route('tires.vehicle.rotation.internal',$vehicle), [
        'pos_a' => 'FL',
        'pos_b' => 'FR'
    ]);
    $response->assertRedirect();

    $this->assertDatabaseHas('tires', ['id'=>$tireA->id,'position'=>'FR']);
    $this->assertDatabaseHas('tires', ['id'=>$tireB->id,'position'=>'FL']);
});
