<?php

use App\Models\{User, Vehicle, OilProduct};
use Illuminate\Support\Carbon;

it('exibe o dashboard de manutenção de óleo', function() {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();
    $product = OilProduct::factory()->create();

    $this->actingAs($user)
        ->get(route('oil.maintenance'))
        ->assertStatus(200)
        ->assertSee('Troca de Óleo - Dashboard')
        ->assertSee($vehicle->plate)
        ->assertSee($product->name);
});

it('registra uma troca de óleo com sucesso', function() {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create(['current_km' => 50000]);
    $product = OilProduct::factory()->create(['stock_quantity' => 20]);

    $odometer = 50500; // maior que current_km para atualizar
    $date = now()->format('Y-m-d');

    $payload = [
        'vehicle_id' => $vehicle->id,
        'oil_product_id' => $product->id,
        'change_date' => $date,
        'odometer_km' => $odometer,
        'quantity_used' => 4,
        'interval_km_used' => 10000,
        'interval_days_used' => 180,
        'notes' => 'Troca preventiva'
    ];

    $this->actingAs($user)
        ->post(route('oil.logs.store'), $payload)
        ->assertRedirect(route('oil.maintenance'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('oil_change_logs', [
        'vehicle_id' => $vehicle->id,
        'odometer_km' => $odometer,
        'next_change_km' => $odometer + 10000,
    ]);

    // Verifica decremento de estoque
    $this->assertDatabaseHas('oil_products', [
        'id' => $product->id,
        'stock_quantity' => 16, // 20 - 4
    ]);

    // Veículo atualizado
    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'current_km' => $odometer,
    ]);
});

