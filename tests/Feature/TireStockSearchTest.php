<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Role, Tire, Vehicle};
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeUserWithRoleId(int $roleId = 1): User {
    // Garante que exista uma Role com o ID desejado
    DB::table('roles')->insertOrIgnore(['id'=>$roleId,'name'=>'role-'.$roleId,'description'=>'test role']);
    /** @var User $user */
    $user = User::factory()->create(['role_id'=>$roleId]);
    return $user;
}

it('retorna pneus em estoque filtrando por múltiplos campos', function () {
    $user = makeUserWithRoleId(1); // role id <=3 para autorização

    // Pneus que devem aparecer
    $tire1 = Tire::factory()->create([
        'serial_number' => 'ABC12345',
        'brand' => 'Michelin',
        'model' => 'XZY100',
        'dimension' => '275/80 R22.5',
        'status' => 'stock',
        'current_vehicle_id' => null,
    ]);
    $tire2 = Tire::factory()->create([
        'serial_number' => 'DEF67890',
        'brand' => 'Pirelli',
        'model' => 'R88',
        'dimension' => '195/55 R16',
        'status' => 'stock',
        'current_vehicle_id' => null,
    ]);

    // Pneu instalado (não deve aparecer)
    $vehicle = Vehicle::factory()->create();
    $inUse = Tire::factory()->create([
        'serial_number' => 'INUSE999',
        'brand' => 'Michelin',
        'status' => 'in_use',
        'current_vehicle_id' => $vehicle->id,
        'position' => 'FL'
    ]);

    $this->actingAs($user);

    // Busca por marca + parte da dimensão (tokenização leva a AND entre tokens)
    $response = $this->get(route('tires.search-stock', ['q' => 'Michelin 275']));
    $response->assertOk();
    $data = $response->json('data');

    expect(collect($data)->pluck('id'))->toContain($tire1->id)
        ->and(collect($data)->pluck('id'))->not()->toContain($inUse->id);

    // Busca por serial_number parcial
    $response2 = $this->get(route('tires.search-stock', ['q' => 'DEF678']));
    $response2->assertOk();
    $ids2 = collect($response2->json('data'))->pluck('id');
    expect($ids2)->toContain($tire2->id);

    // Busca por ID numérico direto
    $response3 = $this->get(route('tires.search-stock', ['q' => (string)$tire1->id]));
    $response3->assertOk();
    $ids3 = collect($response3->json('data'))->pluck('id');
    expect($ids3)->toContain($tire1->id);
});

it('limita a quantidade de resultados retornados (parametro limit)', function () {
    $user = makeUserWithRoleId(1);
    Tire::factory()->count(40)->create([
        'status'=>'stock',
        'current_vehicle_id'=>null,
        'brand'=>'BrandX'
    ]);
    $this->actingAs($user);
    $response = $this->get(route('tires.search-stock', ['q' => 'BrandX', 'limit' => 10]));
    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(10);
});
