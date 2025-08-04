<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

use function Laravel\Prompts\progress;

class HotelOperatorSeeder extends Seeder
{
    public function run(): void
    {
        $operatorAdminRole = Role::where('name', 'Operator Admin')->first();

        if (! $operatorAdminRole) {
            $this->command->error('Operator Admin role not found. Please run RolePermissionSeeder first.');

            return;
        }

        $hotelOperatorCount = 100;

        // Generate cities for hotels
        $cities = $this->generateCities(10);

        progress(
            label: 'Creating hotel operators',
            steps: $hotelOperatorCount,
            callback: function () use ($operatorAdminRole, $cities): string {
                static $counter = 0;
                $counter++;

                // Create hotel operator
                $operator = Operator::create([
                    'name' => fake()->company() . ' ' . fake()->randomElement(['Hotel', 'Resort', 'Inn', 'Lodge', 'Palace']),
                    'type' => OperatorType::Hotel,
                    'status' => OperatorStatus::Approved,
                    'contact_email' => fake()->unique()->safeEmail(),
                    'contact_phone' => fake()->phoneNumber(),
                    'description' => fake()->paragraph(),
                    'metadata' => [
                        'website' => fake()->url(),
                        'founded' => fake()->year(1980),
                        'rating' => fake()->randomFloat(1, 2.5, 5.0),
                        'star_rating' => fake()->numberBetween(2, 5),
                        'city' => fake()->randomElement($cities),
                        'notifications' => [
                            'email' => true,
                            'sms' => fake()->boolean(),
                        ],
                        'booking_settings' => [
                            'advance_booking_days' => fake()->numberBetween(1, 365),
                            'cancellation_policy' => fake()->randomElement(['flexible', 'moderate', 'strict']),
                        ],
                        'amenities' => fake()->randomElements([
                            'Swimming Pool', 'Gym', 'Spa', 'Restaurant', 'Bar', 'WiFi',
                            'Parking', 'Room Service', 'Laundry', 'Conference Room',
                            'Business Center', 'Concierge', 'Airport Shuttle',
                        ], fake()->numberBetween(3, 8)),
                    ],
                ]);

                // Create operator admin user
                $user = User::factory()->create([
                    'name' => fake()->name(),
                    'email' => 'hotel_operator_' . $counter . '@example.com',
                ]);

                $user->operators()->attach($operator->id, ['role_id' => $operatorAdminRole->id]);

                return "Created hotel operator: {$operator->name}";
            },
        );

        $this->command->info('✅ Hotel operators created successfully!');
    }

    private function generateCities(int $count): array
    {
        $cities = [];
        for ($i = 0; $i < $count; $i++) {
            $cities[] = fake()->unique()->city();
        }
        fake()->unique(true); // Reset unique constraint for other uses

        return $cities;
    }
}
