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
            'bus_manage' => 'Can manage buses for an operator',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name, 'description' => $description]);
        }

        // Create Admin role
        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
        ], [
            'is_default' => true,
        ]);

        // Attach all permissions to Admin role
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create User role (default for new users)
        $userRole = Role::firstOrCreate([
            'name' => 'User',
        ], [
            'is_default' => true,
        ]);

        // Users don't get any special permissions by default
        $userRole->permissions()->sync([]);

        // Create Operator-specific roles
        $operatorAdminRole = Role::firstOrCreate([
            'name' => 'Operator Admin',
        ], [
            'is_default' => true,
        ]);

        $operatorAdminRole->permissions()->sync([
            Permission::where('name', 'bus_manage')->first()->id,
        ]);

        $operatorStaffRole = Role::firstOrCreate([
            'name' => 'Operator Staff',
        ], [
            'is_default' => true,
        ]);
        $operatorStaffRole->permissions()->sync([
            Permission::where('name', 'bus_manage')->first()->id,
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
