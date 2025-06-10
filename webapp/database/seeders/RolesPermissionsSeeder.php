<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds_
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
            'map_view',
            'map_create',
            'map_edit',
            'map_delete',

            // Layers permissions
            'layers_view',
            'layers_create',
            'layers_edit',
            'layers_delete',

            // Assets permissions
            'assets_view',
            'assets_create',
            'assets_edit',
            'assets_delete',

            // Alerts permissions
            'alerts_view',
            'alerts_create',
            'alerts_edit',
            'alerts_delete',
            'alerts_manage',

            // History permissions
            'history_view',
            'history_export',

            // Simulations permissions
            'simulations_view',
            'simulations_create',
            'simulations_edit',
            'simulations_delete',
            'simulations_run',

            // Analytics permissions
            'analytics_view',
            'analytics_export',
            'analytics_advanced',

            // Integrations permissions
            'integrations_view',
            'integrations_create',
            'integrations_edit',
            'integrations_delete',

            // Users permissions
            'users_view',
            'users_create',
            'users_edit',
            'users_delete',

            // Permissions management
            'permissions_view',
            'permissions_assign',
            'permissions_edit',

            // Settings permissions
            'settings_view',
            'settings_edit',
            'settings_advanced',
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
            'map_view',
            'map_create',
            'map_edit',
            'map_delete',
            'layers_view',
            'layers_create',
            'layers_edit',
            'layers_delete',
            'assets_view',
            'assets_create',
            'assets_edit',
            'assets_delete',
            'alerts_view',
            'alerts_create',
            'alerts_edit',
            'alerts_delete',
            'alerts_manage',
            'history_view',
            'history_export',
            'simulations_view',
            'simulations_create',
            'simulations_edit',
            'simulations_delete',
            'simulations_run',
            'analytics_view',
            'analytics_export',
            'analytics_advanced',
            'integrations_view',
            'integrations_create',
            'integrations_edit',
            'integrations_delete',
            'users_view',
            'users_create',
            'users_edit',
            'users_delete',
            'permissions_view',
            'permissions_assign',
            'settings_view',
            'settings_edit',
        ];
        $adminRole->syncPermissions($adminPermissions);

        // Assign limited permissions to the user role
        $userPermissions = [
            'map_view',
            'layers_view',
            'assets_view',
            'alerts_view',
            'history_view',
            'simulations_view',
            'simulations_run',
            'analytics_view',
            'integrations_view',
            'settings_view',
        ];

        $userRole->syncPermissions($userPermissions);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('- Root: ' . $rootRole->permissions->count() . ' permissions');
        $this->command->info('- Administrator: ' . $adminRole->permissions->count() . ' permissions');
        $this->command->info('- User: ' . $userRole->permissions->count() . ' permissions');

        foreach (User::all() as $user) {
            $user->assignRole('user');
        }

        $this->command->info('All users assigned to the root role_');
    }
}
