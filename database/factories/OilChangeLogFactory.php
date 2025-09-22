<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{OilChangeLog, Vehicle, OilProduct, User};
use Carbon\Carbon;

class OilChangeLogFactory extends Factory
{
    protected $model = OilChangeLog::class;

    public function definition(): array
    {
        $changeDate = Carbon::now()->subDays($this->faker->numberBetween(1,90));
        $intervalKm = 10000; $intervalDays = 180;
        $odometer = $this->faker->numberBetween(10000,120000);
        return [
            'vehicle_id' => Vehicle::factory(),
            'oil_product_id' => OilProduct::factory(),
            'user_id' => User::factory(),
            'change_date' => $changeDate->format('Y-m-d'),
            'odometer_km' => $odometer,
            'quantity_used' => $this->faker->randomFloat(2, 2, 6),
            'unit_cost_at_time' => $this->faker->randomFloat(2,20,80),
            'total_cost' => function(array $attrs){ return $attrs['unit_cost_at_time'] * $attrs['quantity_used']; },
            'next_change_km' => $odometer + $intervalKm,
            'next_change_date' => $changeDate->copy()->addDays($intervalDays)->format('Y-m-d'),
            'interval_km_used' => $intervalKm,
            'interval_days_used' => $intervalDays,
            'notes' => $this->faker->sentence()
        ];
    }
}

