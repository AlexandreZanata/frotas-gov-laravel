<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{Vehicle, FuelType, Secretariat, Department, VehicleCategory, VehicleStatus};

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $brand = $this->faker->randomElement(['Ford','Chevrolet','Fiat','VW','Toyota']);
        $model = $this->faker->randomElement(['Ranger','Onix','Uno','Gol','Corolla']);
        return [
            'brand' => $brand,
            'model' => $model,
            'year' => $this->faker->numberBetween(2015, 2025),
            'plate' => strtoupper($this->faker->bothify('???-####')),
            'renavam' => $this->faker->unique()->numerify('###########'),
            'chassi' => strtoupper($this->faker->bothify('#################')),
            'prefix' => strtoupper($this->faker->bothify('PRF-###')),
            'current_km' => $this->faker->numberBetween(10000, 120000),
            'fuel_type_id' => FuelType::factory(),
            'document_path' => null,
            'vehicle_category_id' => VehicleCategory::factory(),
            'current_secretariat_id' => Secretariat::factory(),
            'current_department_id' => Department::factory(),
            'tank_capacity' => $this->faker->randomFloat(2, 30, 80),
            'avg_km_per_liter' => $this->faker->randomFloat(2, 5, 15),
            'status' => 'Disponível',
        ];
    }
}

