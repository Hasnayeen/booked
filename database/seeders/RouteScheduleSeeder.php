<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Route;
use App\Models\RouteSchedule;
use Illuminate\Database\Seeder;

use function Laravel\Prompts\progress;

class RouteScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $routes = Route::with('operator')->get();
        $buses = Bus::with('operator')->get();

        if ($routes->isEmpty()) {
            $this->command->error('No routes found. Please run RouteSeeder first.');

            return;
        }

        if ($buses->isEmpty()) {
            $this->command->error('No buses found. Please run BusSeeder first.');

            return;
        }

        // Group buses by operator for efficient assignment
        $busesByOperator = $buses->groupBy('operator_id');

        $scheduleAssignments = [];

        // Pre-calculate schedule assignments
        foreach ($routes as $route) {
            $scheduleCount = fake()->numberBetween(2, 8);
            $operatorBuses = $busesByOperator->get($route->operator_id, collect());

            if ($operatorBuses->isEmpty()) {
                continue; // Skip if no buses for this operator
            }

            for ($i = 0; $i < $scheduleCount; $i++) {
                $bus = $operatorBuses->random(); // Assign random bus from operator's fleet
                $scheduleAssignments[] = [
                    'route' => $route,
                    'bus' => $bus,
                ];
            }
        }

        $totalSchedules = count($scheduleAssignments);

        progress(
            label: 'Creating route schedules',
            steps: $totalSchedules,
            callback: function () use ($scheduleAssignments) {
                static $scheduleIndex = 0;

                if ($scheduleIndex >= count($scheduleAssignments)) {
                    return;
                }

                $assignment = $scheduleAssignments[$scheduleIndex];
                $route = $assignment['route'];
                $bus = $assignment['bus'];

                // Generate schedule times
                $departureHour = fake()->numberBetween(5, 23);
                $departureMinute = fake()->randomElement([0, 15, 30, 45]);
                $departureTime = sprintf('%02d:%02d:00', $departureHour, $departureMinute);

                // Calculate arrival time (3-12 hours later)
                $travelHours = fake()->numberBetween(3, 12);
                $arrivalHour = ($departureHour + $travelHours) % 24;
                $arrivalMinute = $departureMinute;
                $arrivalTime = sprintf('%02d:%02d:00', $arrivalHour, $arrivalMinute);

                $schedule = RouteSchedule::create([
                    'operator_id' => $route->operator_id,
                    'route_id' => $route->id,
                    'bus_id' => $bus->id,
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                    'off_days' => fake()->boolean(20) ? [fake()->randomElement(['Sunday', 'Monday'])] : [],
                    'is_active' => true,
                    'metadata' => [
                        'boarding_time_before_departure' => '30 minutes',
                        'check_in_closes_before' => '10 minutes',
                        'special_instructions' => fake()->boolean(30) ? fake()->sentence() : null,
                    ],
                ]);

                $scheduleIndex++;

                return "Created schedule {$departureTime} for {$route->route_name} using {$bus->bus_number}";
            },
        );

        $this->command->info('✅ Route schedules created successfully!');
    }
}
