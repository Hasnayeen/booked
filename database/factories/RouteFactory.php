<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Operator;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        $departureTime = $this->faker->time('H:i');
        $arrivalTime = $this->faker->time('H:i');

        // Ensure arrival is after departure (simple same-day calculation)
        $departure = \Carbon\Carbon::createFromFormat('H:i', $departureTime);
        $arrival = \Carbon\Carbon::createFromFormat('H:i', $arrivalTime);

        if ($arrival->lessThanOrEqualTo($departure)) {
            $arrival = $departure->copy()->addHours(rand(1, 8));
        }

        $duration = $arrival->diff($departure);

        $originCity = $this->faker->city();
        $destinationCity = $this->faker->city();

        return [
            'operator_id' => Operator::factory(),
            'bus_id' => Bus::factory(),
            'route_name' => $originCity . ' to ' . $destinationCity,
            'origin_city' => $originCity,
            'destination_city' => $destinationCity,
            'departure_time' => $departureTime,
            'arrival_time' => $arrival->format('H:i'),
            'distance_km' => $this->faker->randomFloat(1, 50, 800),
            'is_active' => $this->faker->boolean(85),
            'off_days' => $this->faker->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], rand(0, 2)),
            'stops' => $this->faker->randomElements([
                ['stop' => 'Central Station'],
                ['stop' => 'Airport Terminal'],
                ['stop' => 'Main Square'],
                ['stop' => 'Shopping Mall'],
                ['stop' => 'University Campus'],
            ], rand(0, 3)),
            'boarding_points' => $this->faker->randomElements([
                ['point' => 'Platform A'],
                ['point' => 'Gate 1'],
                ['point' => 'Terminal Entrance'],
                ['point' => 'Parking Lot B'],
            ], rand(1, 2)),
            'drop_off_points' => $this->faker->randomElements([
                ['point' => 'Terminal B'],
                ['point' => 'Exit Gate'],
                ['point' => 'Main Entrance'],
                ['point' => 'Station Plaza'],
            ], rand(1, 2)),
            'metadata' => [
                'wifi_available' => $this->faker->boolean(),
                'refreshments' => $this->faker->boolean(),
                'route_type' => $this->faker->randomElement(['express', 'local', 'tourist']),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function overnight(): static
    {
        return $this->state(function (array $attributes) {
            $departureTime = '23:30';
            $arrivalTime = '06:15';

            return [
                'departure_time' => $departureTime,
                'arrival_time' => $arrivalTime,
            ];
        });
    }
}
