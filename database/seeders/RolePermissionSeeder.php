<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'approve_operator' => 'Can approve operator registrations',
            'manage_users' => 'Can manage system users',
            'view_reports' => 'Can view system reports',
            'manage_settings' => 'Can manage system settings',
        ];

        foreach (array_keys($permissions) as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Create Admin role
        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
        ], [
            'is_default' => false,
        ]);

        // Attach all permissions to Admin role
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create Operator Manager role (can only approve operators)
        $operatorManagerRole = Role::firstOrCreate([
            'name' => 'Operator Manager',
        ], [
            'is_default' => false,
        ]);

        $approvePermission = Permission::where('name', 'approve_operator')->first();
        $operatorManagerRole->permissions()->sync([$approvePermission->id]);

        // Create User role (default for new users)
        $userRole = Role::firstOrCreate([
            'name' => 'User',
        ], [
            'is_default' => true,
        ]);

        // Users don't get any special permissions by default
        $userRole->permissions()->sync([]);

        // Create Operator-specific roles
        Role::firstOrCreate([
            'name' => 'Operator Admin',
        ], [
            'is_default' => false,
        ]);

        Role::firstOrCreate([
            'name' => 'Operator Member',
        ], [
            'is_default' => false,
        ]);

        Role::firstOrCreate([
            'name' => 'Operator Staff',
        ], [
            'is_default' => false,
        ]);

        Role::firstOrCreate([
            'name' => 'Operator Viewer',
        ], [
            'is_default' => false,
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
