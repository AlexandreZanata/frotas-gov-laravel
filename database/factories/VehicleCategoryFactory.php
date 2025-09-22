<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\VehicleCategory;

class VehicleCategoryFactory extends Factory
{
    protected $model = VehicleCategory::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'layout_key' => 'car_2x2',
            'oil_change_km' => 10000,
            'oil_change_days' => 180,
        ];
    }
}

