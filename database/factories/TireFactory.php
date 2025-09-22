<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tire;

class TireFactory extends Factory
{
    protected $model = Tire::class;

    public function definition(): array
    {
        return [
            'serial_number' => strtoupper($this->faker->bothify('PN-######')),
            'brand' => $this->faker->randomElement(['Pirelli','Goodyear','Bridgestone','Michelin','Firestone']),
            'model' => $this->faker->lexify('Modelo-??'),
            'dimension' => $this->faker->randomElement(['195/55 R16','205/60 R16','275/80 R22.5']),
            'purchase_date' => $this->faker->date(),
            'initial_tread_depth_mm' => $this->faker->randomFloat(2,6,12),
            'current_tread_depth_mm' => $this->faker->randomFloat(2,3,12),
            'expected_tread_life_km' => 60000,
            'accumulated_km' => $this->faker->numberBetween(0,55000),
            'status' => 'stock',
            'life_cycles' => 0,
        ];
    }
}

