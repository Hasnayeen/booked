<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Models\Bus;
use App\Models\Operator;
use App\Models\Route;
use Illuminate\Database\Seeder;

use function Laravel\Prompts\progress;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $routes = Route::with('operator')->get();

        if ($routes->isEmpty()) {
            $this->command->error('No routes found. Please run RouteSeeder first.');

            return;
        }

        // Calculate total buses needed (2-8 per route)
        $totalBuses = 0;
        $routeBusNeeds = [];

        foreach ($routes as $route) {
            $busCount = fake()->numberBetween(2, 8);
            $routeBusNeeds[$route->id] = $busCount;
            $totalBuses += $busCount;
        }

        progress(
            label: 'Creating buses for routes',
            steps: $totalBuses,
            callback: function () use ($routes, $routeBusNeeds) {
                static $currentRouteIndex = 0;
                static $busesCreatedForCurrentRoute = 0;

                if ($currentRouteIndex >= $routes->count()) {
                    return;
                }

                $route = $routes[$currentRouteIndex];
                $busesNeeded = $routeBusNeeds[$route->id];

                $bus = $this->createBusForOperator($route->operator);

                $busesCreatedForCurrentRoute++;

                if ($busesCreatedForCurrentRoute >= $busesNeeded) {
                    $currentRouteIndex++;
                    $busesCreatedForCurrentRoute = 0;
                }

                return "Created bus {$bus->bus_number} for {$route->operator->name}";
            },
        );

        $this->command->info('✅ Buses created successfully!');
    }

    private function createBusForOperator(Operator $operator): Bus
    {
        $category = fake()->randomElement(BusCategory::cases());
        $type = $category === BusCategory::Economy
            ? fake()->randomElement(BusType::cases())
            : BusType::Ac;

        $totalSeats = match ($category) {
            BusCategory::Economy => fake()->numberBetween(40, 55),
            BusCategory::Business => fake()->numberBetween(32, 45),
            BusCategory::Luxury => fake()->numberBetween(24, 36),
            BusCategory::Sleeper => fake()->numberBetween(20, 32),
        };

        return Bus::create([
            'operator_id' => $operator->id,
            'bus_number' => 'BUS-' . fake()->unique()->numerify('####'),
            'category' => $category,
            'type' => $type,
            'license_plate' => fake()->regexify('[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}'),
            'total_seats' => $totalSeats,
            'is_active' => true,
            'amenities' => $this->getAmenitiesForCategory($category, $type),
            'seat_config' => $this->generateSeatConfig($totalSeats),
            'metadata' => [
                'year' => fake()->numberBetween(2018, 2024),
                'manufacturer' => fake()->randomElement(['Volvo', 'Mercedes-Benz', 'Scania', 'Tata', 'Ashok Leyland']),
                'fuel_type' => fake()->randomElement(['Diesel', 'CNG', 'Electric']),
                'insurance_expires' => fake()->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
                'last_maintenance' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            ],
        ]);
    }

    private function getAmenitiesForCategory(BusCategory $category, BusType $type): array
    {
        $baseAmenities = ['GPS Tracking', 'First Aid Kit', 'Fire Extinguisher'];

        if ($type === BusType::Ac) {
            $baseAmenities[] = 'Air Conditioning';
        }

        $categoryAmenities = match ($category) {
            BusCategory::Economy => ['Reading Lights', 'Mobile Charging Points'],
            BusCategory::Business => ['WiFi', 'Reading Lights', 'Mobile Charging Points', 'Comfortable Seating', 'Water Bottles'],
            BusCategory::Luxury => ['WiFi', 'Entertainment System', 'Reclining Seats', 'Mobile Charging Points', 'Snack Service', 'Blankets', 'Pillows'],
            BusCategory::Sleeper => ['Bed Sheets', 'Pillows', 'Privacy Curtains', 'Mobile Charging Points', 'Reading Lights', 'Individual AC Vents'],
        };

        return array_merge($baseAmenities, $categoryAmenities);
    }

    private function generateSeatConfig(int $totalSeats): array
    {
        // Determine if single or double deck based on seat count
        $deckType = $totalSeats > 40 ? '2' : '1'; // Double deck for more than 40 seats

        if ($deckType === '1') {
            // Single deck configuration
            $totalRows = (int) ceil($totalSeats / 4);
            $totalRows = max(5, min(10, $totalRows)); // Ensure between 5-10 rows

            return [
                'deck_type' => '1',
                'lower_deck' => [
                    'seat_type' => '1', // Regular seats
                    'total_columns' => 4,
                    'column_label' => 'alpha',
                    'column_layout' => '2:2',
                    'total_rows' => $totalRows,
                    'row_label' => 'numeric',
                    'price_per_seat_in_cents' => fake()->numberBetween(50000, 150000), // $500-$1500
                ],
            ];
        }
        // Double deck configuration
        $rowsPerDeck = (int) ceil($totalSeats / 8);
        // 4 seats per row per deck
        $rowsPerDeck = max(5, min(10, $rowsPerDeck));
        return [
            'deck_type' => '2',
            'lower_deck' => [
                'seat_type' => '1',
                'total_columns' => 4,
                'column_label' => 'alpha',
                'column_layout' => '2:2',
                'total_rows' => $rowsPerDeck,
                'row_label' => 'numeric',
                'price_per_seat_in_cents' => fake()->numberBetween(60000, 180000),
            ],
            'upper_deck' => [
                'seat_type' => '1',
                'total_columns' => 4,
                'column_label' => 'alpha',
                'column_layout' => '2:2',
                'total_rows' => $rowsPerDeck,
                'row_label' => 'numeric',
                'price_per_seat_in_cents' => fake()->numberBetween(60000, 180000),
            ],
        ];
    }
}
