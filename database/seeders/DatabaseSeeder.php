<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset Faker's unique constraint to avoid duplicate issues
        fake()->unique(true);

        // Call the role permission seeder first
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create a test admin user
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Assign admin role to the user
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser->roles()->attach($adminRole->id);

        // Create a test regular user
        $regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);

        // Assign user role to the regular user
        $userRole = Role::where('name', 'User')->first();
        $regularUser->roles()->attach($userRole->id);

        // Create comprehensive data using separate seeders
        $this->call([
            BusOperatorSeeder::class,
            HotelOperatorSeeder::class,
            RouteSeeder::class,
            BusSeeder::class,
            RouteScheduleSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin user: admin@example.com');
        $this->command->info('Regular user: user@example.com');
        $this->command->info('Bus operator users: bus_operator_1@example.com to bus_operator_20@example.com');
        $this->command->info('Hotel operator users: hotel_operator_1@example.com to hotel_operator_100@example.com');
        $this->command->info('Default password: password');
    }
}
