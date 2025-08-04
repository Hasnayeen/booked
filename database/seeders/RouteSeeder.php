<?php

namespace Database\Seeders;

use App\Enums\OperatorType;
use App\Models\Operator;
use App\Models\Route;
use Illuminate\Database\Seeder;

use function Laravel\Prompts\progress;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $busOperators = Operator::where('type', OperatorType::Bus)->get();

        if ($busOperators->isEmpty()) {
            $this->command->error('No bus operators found. Please run BusOperatorSeeder first.');

            return;
        }

        // Generate cities for routes
        $cities = $this->generateCities(10);
        $cityCombinations = $this->generateCityCombinations($cities);

        $totalRoutes = $busOperators->count() * 16; // 16 routes per operator

        // Pre-assign city pairs to operators
        $operatorCityPairs = [];
        foreach ($busOperators as $index => $operator) {
            $operatorCityPairs[$operator->id] = array_slice($cityCombinations, ($index % count($cityCombinations)), 8);
        }

        progress(
            label: 'Creating routes for bus operators',
            steps: $totalRoutes,
            callback: function () use ($busOperators, $operatorCityPairs): string {
                static $routeCounter = 0;
                static $operatorIndex = 0;
                static $cityPairIndex = 0;
                static $direction = 0; // 0 = forward, 1 = return

                $operator = $busOperators[$operatorIndex];
                $cityPairs = $operatorCityPairs[$operator->id];
                $cityPair = $cityPairs[intval($cityPairIndex / 2)];
                [$originCity, $destinationCity] = $direction === 0 ? $cityPair : array_reverse($cityPair);

                $route = Route::create([
                    'operator_id' => $operator->id,
                    'route_name' => $originCity . ' to ' . $destinationCity,
                    'origin_city' => $originCity,
                    'destination_city' => $destinationCity,
                    'distance_km' => fake()->randomFloat(1, 100, 800),
                    'is_active' => true,
                    'stops' => $this->generateStops(),
                    'boarding_points' => [
                        ['point' => $originCity . ' Central Station'],
                        ['point' => $originCity . ' Bus Terminal'],
                    ],
                    'drop_off_points' => [
                        ['point' => $destinationCity . ' Central Station'],
                        ['point' => $destinationCity . ' Bus Terminal'],
                    ],
                    'metadata' => [
                        'wifi_available' => fake()->boolean(70),
                        'refreshments' => fake()->boolean(60),
                        'route_type' => fake()->randomElement(['express', 'local']),
                        'estimated_duration' => fake()->numberBetween(3, 12) . ' hours',
                    ],
                ]);

                $routeCounter++;
                $direction = 1 - $direction; // Toggle direction

                if ($direction === 0) {
                    $cityPairIndex++;
                }

                if ($cityPairIndex >= 16) { // 8 pairs * 2 directions = 16 routes per operator
                    $operatorIndex++;
                    $cityPairIndex = 0;
                }

                return "Created route: {$route->route_name} for {$operator->name}";
            },
        );

        $this->command->info('✅ Routes created successfully!');
    }

    private function generateCities(int $count): array
    {
        $cities = [];
        for ($i = 0; $i < $count; $i++) {
            $cities[] = fake()->unique()->city();
        }
        fake()->unique(true); // Reset unique constraint

        return $cities;
    }

    private function generateCityCombinations(array $cities): array
    {
        $combinations = [];
        $counter = count($cities);

        for ($i = 0; $i < $counter; $i++) {
            for ($j = $i + 1; $j < count($cities); $j++) {
                $combinations[] = [$cities[$i], $cities[$j]];
            }
        }

        // Shuffle to randomize distribution
        shuffle($combinations);

        return $combinations;
    }

    private function generateStops(): array
    {
        $stops = [];
        $stopCount = fake()->numberBetween(0, 3);

        for ($i = 0; $i < $stopCount; $i++) {
            $stops[] = [
                'stop' => fake()->randomElement([
                    'Highway Rest Stop',
                    'Petrol Station',
                    'District Center',
                    'Junction Point',
                    'Tourist Spot',
                ]) . ' ' . fake()->numberBetween(1, 10),
            ];
        }

        return $stops;
    }
}
