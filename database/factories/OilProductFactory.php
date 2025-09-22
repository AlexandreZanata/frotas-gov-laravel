<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OilProduct;

class OilProductFactory extends Factory
{
    protected $model = OilProduct::class;

    public function definition(): array
    {
        return [
            'name' => 'Óleo '.$this->faker->unique()->bothify('##W##'),
            'code' => strtoupper($this->faker->bothify('OL-####')),
            'brand' => $this->faker->randomElement(['Shell','Ipiranga','Petrobras','Castrol']),
            'viscosity' => $this->faker->randomElement(['5W30','10W40','15W40']),
            'stock_quantity' => $this->faker->numberBetween(5, 50),
            'reorder_level' => 5,
            'unit_cost' => $this->faker->randomFloat(2, 20, 80),
            'recommended_interval_km' => 10000,
            'recommended_interval_days' => 180,
            'description' => $this->faker->sentence(),
        ];
    }
}

