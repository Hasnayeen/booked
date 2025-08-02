<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Operator;
use App\Models\Route;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

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

        $arrival->diff($departure);

        $originCity = fake()->city();
        $destinationCity = fake()->city();

        return [
            'operator_id' => Operator::factory(),
            'bus_id' => Bus::factory(),
            'route_name' => $originCity . ' to ' . $destinationCity,
            'origin_city' => $originCity,
            'destination_city' => $destinationCity,
            'departure_time' => $departureTime,
            'arrival_time' => $arrival->format('H:i'),
            'distance_km' => fake()->randomFloat(1, 50, 800),
            'is_active' => fake()->boolean(85),
            'off_days' => fake()->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], random_int(0, 2)),
            'stops' => fake()->randomElements([
                ['stop' => 'Central Station'],
                ['stop' => 'Airport Terminal'],
                ['stop' => 'Main Square'],
                ['stop' => 'Shopping Mall'],
                ['stop' => 'University Campus'],
            ], random_int(0, 3)),
            'boarding_points' => fake()->randomElements([
                ['point' => 'Platform A'],
                ['point' => 'Gate 1'],
                ['point' => 'Terminal Entrance'],
                ['point' => 'Parking Lot B'],
            ], random_int(1, 2)),
            'drop_off_points' => fake()->randomElements([
                ['point' => 'Terminal B'],
                ['point' => 'Exit Gate'],
                ['point' => 'Main Entrance'],
                ['point' => 'Station Plaza'],
            ], random_int(1, 2)),
            'metadata' => [
                'wifi_available' => fake()->boolean(),
                'refreshments' => fake()->boolean(),
                'route_type' => fake()->randomElement(['express', 'local', 'tourist']),
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
}
