<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define only the relevant permissions
        $permissions = [
            // Maps
            'map_view',
            'map_create',
            'map_edit',
            'map_delete',

            // Layers
            'layers_view',
            'layers_create',
            'layers_edit',
            'layers_delete',

            // Users
            'users_view',
            'users_create',
            'users_edit',
            'users_delete',

            // Permissions management
            'permissions_view',
            'permissions_assign',
            'permissions_edit',

            // Settings
            'settings_view',
            'settings_edit',
            'settings_advanced',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create or get roles
        $rootRole = Role::firstOrCreate(['name' => 'root']);
        $adminRole = Role::firstOrCreate(['name' => 'administrator']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Sync permissions for each role
        $rootRole->syncPermissions(Permission::all());

        $adminPermissions = [
            'map_view',
            'map_create',
            'map_edit',
            'map_delete',
            'layers_view',
            'layers_create',
            'layers_edit',
            'layers_delete',
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

        $userPermissions = [
            'map_view',
            'layers_view',
            'users_view',
            'permissions_view',
            'settings_view',
        ];
        $userRole->syncPermissions($userPermissions);

        // Assign the 'user' role to all existing users if they don't have it
        foreach (User::all() as $user) {
            if (!$user->hasRole('user')) {
                $user->assignRole('user');
            }
        }

        $this->command->info('Roles and permissions ensured successfully!');
        $this->command->info('- Root: ' . $rootRole->permissions->count() . ' permissions');
        $this->command->info('- Administrator: ' . $adminRole->permissions->count() . ' permissions');
        $this->command->info('- User: ' . $userRole->permissions->count() . ' permissions');
        $this->command->info('All users assigned to the user role if not already assigned.');
    }
}