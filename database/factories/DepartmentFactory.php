<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Department;
use App\Models\Secretariat;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'secretariat_id' => Secretariat::factory(),
            'name' => $this->faker->unique()->words(2, true),
        ];
    }
}

