<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create root user
        $rootUser = User::create([
            'name' => 'Root User',
            'email' => 'root@geoglify.pt',
            'password' => bcrypt('root@geoglify.pt'),
            'email_verified_at' => now(),
            'created_by' => null, // No creator for root user
            'updated_by' => null, // No updater for root user
            'deleted_by' => null, // No deleter for root user
        ]);

        // Find or create the root role
        $rootRole = Role::firstOrCreate(['name' => 'root']);

        // Assign the root role to the root user
        $rootUser->assignRole($rootRole);

        // Create additional users with the admin role and assign them the role
        $adminUsers = [
            [
                'name' => 'Admin User 1',
                'email' => 'admin1@geoglify.pt',
                'password' => bcrypt('admin1234'),
                'email_verified_at' => now(),
                'created_by' => $rootUser->id,
                'updated_by' => $rootUser->id,
                'deleted_by' => null, // No deleter for admin users
            ],
            [
                'name' => 'Admin User 2',
                'email' => 'admin2@geoglify.pt',
                'password' => bcrypt('admin1234'),
                'email_verified_at' => now(),
                'created_by' => $rootUser->id,
                'updated_by' => $rootUser->id,
                'deleted_by' => null, // No deleter for admin users
            ],
            [
                'name' => 'Admin User 3',
                'email' => 'admin3@geoglify.pt',
                'password' => bcrypt('admin1234'),
                'email_verified_at' => now(),
                'created_by' => $rootUser->id,
                'updated_by' => $rootUser->id,
                'deleted_by' => null, // No deleter for admin users
            ],
            [
                'name' => 'Admin User 4',
                'email' => 'admin4@geoglify.pt',
                'password' => bcrypt('admin1234'),
                'email_verified_at' => now(),
                'created_by' => $rootUser->id,
                'updated_by' => $rootUser->id,
                'deleted_by' => null, // No deleter for admin users
            ]
        ];

        foreach ($adminUsers as $adminData) {
            $adminUser = User::create($adminData);
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $adminUser->assignRole($adminRole);
        }

        // Create a regular users with the user role and assign them the role
        $regularUsers = [
            [
                'name' => 'Regular User 1',
                'email' => 'regular@geoglify.pt',
                'password' => bcrypt('user1234'),
                'email_verified_at' => now(),
                'created_by' => $rootUser->id,
                'updated_by' => $rootUser->id,
                'deleted_by' => null, // No deleter for regular users
            ],
            [
                'name' => 'Regular User 2',
                'email' => 'regular2@geoglify.pt',
                'password' => bcrypt('user1234'),
                'email_verified_at' => now(),
                'created_by' => $rootUser->id,
                'updated_by' => $rootUser->id,
                'deleted_by' => null, // No deleter for regular users
            ]
        ];

        foreach ($regularUsers as $userData) {
            $user = User::create($userData);
            $userRole = Role::firstOrCreate(['name' => 'user']);
            $user->assignRole($userRole);
        }
    }
}
