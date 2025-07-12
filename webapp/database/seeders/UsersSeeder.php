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
        // Check if root user already exists
        $rootUser = User::where('email', 'root@geoglify.pt')->first();

        if (!$rootUser) {
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

            $this->command->info('Root user created.');
        } else {
            $this->command->info('Root user already exists, skipping creation.');
        }

        // Find or create the root role
        $rootRole = Role::firstOrCreate(['name' => 'root']);

        // Assign the root role to the root user (even if it existed)
        if (!$rootUser->hasRole('root')) {
            $rootUser->assignRole($rootRole);
            $this->command->info('Root role assigned to root user.');
        } else {
            $this->command->info('Root user already has root role.');
        }
    }
}
