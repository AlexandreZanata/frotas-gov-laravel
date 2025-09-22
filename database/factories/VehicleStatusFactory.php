<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\VehicleStatus;
use Illuminate\Support\Str;

class VehicleStatusFactory extends Factory
{
    protected $model = VehicleStatus::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Disponível','Em uso','Manutenção','Inativo']);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => $this->faker->randomElement(['success','warning','danger','secondary']),
        ];
    }
}
