<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Operator;
use App\Models\Route;
use App\Models\RouteSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouteSchedule>
 */
class RouteScheduleFactory extends Factory
{
    protected $model = RouteSchedule::class;

    public function definition(): array
    {
        $departureTime = fake()->time('H:i');
        $arrivalTime = fake()->time('H:i');

        // Ensure arrival is after departure (simple same-day calculation)
        $departure = Carbon::createFromFormat('H:i', $departureTime);
        $arrival = Carbon::createFromFormat('H:i', $arrivalTime);

        if ($arrival->lessThanOrEqualTo($departure)) {
            $arrival = $departure->copy()->addHours(random_int(1, 8));
        }

        return [
            'operator_id' => Operator::factory(),
            'route_id' => Route::factory(),
            'bus_id' => Bus::factory(),
            'departure_time' => $departureTime,
            'arrival_time' => $arrival->format('H:i'),
            'is_active' => fake()->boolean(85),
            'off_days' => fake()->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], random_int(0, 2)),
            'metadata' => [
                'driver_name' => fake()->name(),
                'priority' => fake()->randomElement(['high', 'medium', 'low']),
                'express_service' => fake()->boolean(30),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function overnight(): static
    {
        return $this->state(function (array $attributes): array {
            $departureTime = '23:30';
            $arrivalTime = '06:15';

            return [
                'departure_time' => $departureTime,
                'arrival_time' => $arrivalTime,
            ];
        });
    }

    public function earlyMorning(): static
    {
        return $this->state(function (array $attributes): array {
            $departureTime = '06:00';
            $arrivalTime = '10:00';

            return [
                'departure_time' => $departureTime,
                'arrival_time' => $arrivalTime,
            ];
        });
    }

    public function afternoon(): static
    {
        return $this->state(function (array $attributes): array {
            $departureTime = '14:00';
            $arrivalTime = '18:00';

            return [
                'departure_time' => $departureTime,
                'arrival_time' => $arrivalTime,
            ];
        });
    }
}
