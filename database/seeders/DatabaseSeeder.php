<?php

namespace Database\Seeders;

use App\Models\Operator;
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

        // Create an user with an operator
        $operatorRole = Role::where('name', 'Operator Admin')->first();
        $operatorUser = User::factory()->create([
            'name' => 'Operator User',
            'email' => 'operator@example.com',
        ]);
        $operator = Operator::factory()->create();
        $operatorUser->operators()->attach($operator->id, ['role_id' => $operatorRole->id]);

        $this->call([
            BusSeeder::class,
            BookingSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin user: admin@example.com');
        $this->command->info('Regular user: user@example.com');
        $this->command->info('Operator user: operator@example.com');
        $this->command->info('Default password: password');
    }
}
