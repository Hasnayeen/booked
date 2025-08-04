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

class BusOperatorSeeder extends Seeder
{
    public function run(): void
    {
        $operatorAdminRole = Role::where('name', 'Operator Admin')->first();

        if (! $operatorAdminRole) {
            $this->command->error('Operator Admin role not found. Please run RolePermissionSeeder first.');

            return;
        }

        $busOperatorCount = 20;

        progress(
            label: 'Creating bus operators',
            steps: $busOperatorCount,
            callback: function () use ($operatorAdminRole): string {
                static $counter = 0;
                $counter++;

                // Create bus operator
                $operator = Operator::create([
                    'name' => fake()->company() . ' Bus Lines',
                    'type' => OperatorType::Bus,
                    'status' => OperatorStatus::Approved,
                    'contact_email' => fake()->unique()->safeEmail(),
                    'contact_phone' => fake()->phoneNumber(),
                    'description' => fake()->paragraph(),
                    'metadata' => [
                        'website' => fake()->url(),
                        'founded' => fake()->year(1990),
                        'rating' => fake()->randomFloat(1, 3.5, 5.0),
                        'notifications' => [
                            'email' => true,
                            'sms' => fake()->boolean(),
                        ],
                        'booking_settings' => [
                            'advance_booking_days' => fake()->numberBetween(30, 120),
                            'cancellation_policy' => fake()->randomElement(['flexible', 'moderate', 'strict']),
                        ],
                    ],
                ]);

                // Create operator admin user
                $user = User::factory()->create([
                    'name' => fake()->name(),
                    'email' => 'bus_operator_' . $counter . '@example.com',
                ]);

                $user->operators()->attach($operator->id, ['role_id' => $operatorAdminRole->id]);

                return "Created bus operator: {$operator->name}";
            },
        );

        $this->command->info('✅ Bus operators created successfully!');
    }
}
