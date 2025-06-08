<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear all existing roles and permissions
        Role::truncate();
        Permission::truncate();

        // Define permissions for the application
        $permissions = [
            // Map permissions
            'map.view',
            'map.create',
            'map.edit',
            'map.delete',

            // Layers permissions
            'layers.view',
            'layers.create',
            'layers.edit',
            'layers.delete',

            // Assets permissions
            'assets.view',
            'assets.create',
            'assets.edit',
            'assets.delete',

            // Alerts permissions
            'alerts.view',
            'alerts.create',
            'alerts.edit',
            'alerts.delete',
            'alerts.manage',

            // History permissions
            'history.view',
            'history.export',

            // Simulations permissions
            'simulations.view',
            'simulations.create',
            'simulations.edit',
            'simulations.delete',
            'simulations.run',

            // Analytics permissions
            'analytics.view',
            'analytics.export',
            'analytics.advanced',

            // Integrations permissions
            'integrations.view',
            'integrations.create',
            'integrations.edit',
            'integrations.delete',

            // Users permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Permissions management
            'permissions.view',
            'permissions.assign',
            'permissions.edit',

            // Settings permissions
            'settings.view',
            'settings.edit',
            'settings.advanced',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $rootRole = Role::firstOrCreate(['name' => 'root']);
        $adminRole = Role::firstOrCreate(['name' => 'administrator']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign all permissions to the root role
        $rootRole->syncPermissions(Permission::all());

        // Assign specific permissions to the admin and user roles
        $adminPermissions = [
            'map.view',
            'map.create',
            'map.edit',
            'map.delete',
            'layers.view',
            'layers.create',
            'layers.edit',
            'layers.delete',
            'assets.view',
            'assets.create',
            'assets.edit',
            'assets.delete',
            'alerts.view',
            'alerts.create',
            'alerts.edit',
            'alerts.delete',
            'alerts.manage',
            'history.view',
            'history.export',
            'simulations.view',
            'simulations.create',
            'simulations.edit',
            'simulations.delete',
            'simulations.run',
            'analytics.view',
            'analytics.export',
            'analytics.advanced',
            'integrations.view',
            'integrations.create',
            'integrations.edit',
            'integrations.delete',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'permissions.view',
            'permissions.assign',
            'settings.view',
            'settings.edit',
        ];
        $adminRole->syncPermissions($adminPermissions);

        // Assign limited permissions to the user role
        $userPermissions = [
            'map.view',
            'layers.view',
            'assets.view',
            'alerts.view',
            'history.view',
            'simulations.view',
            'simulations.run',
            'analytics.view',
            'integrations.view',
            'settings.view',
        ];

        $userRole->syncPermissions($userPermissions);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('- Root: ' . $rootRole->permissions->count() . ' permissions');
        $this->command->info('- Administrator: ' . $adminRole->permissions->count() . ' permissions');
        $this->command->info('- User: ' . $userRole->permissions->count() . ' permissions');

        foreach (User::all() as $user) {
            $user->assignRole('user');
        }

        $this->command->info('All users assigned to the root role.');
    }
}
