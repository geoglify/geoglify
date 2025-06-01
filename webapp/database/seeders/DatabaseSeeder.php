<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Get the first user or default to user ID 1
        $userId = User::first()?->id ?? 1;

        // Create a layer with point, line, and polygon features
        $layerId = DB::table('layers')->insertGetId([
            'name' => 'Example Layer',
            'description' => 'Layer with point, line and polygon features',
            'geometry_type' => 'GEOMETRY',
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Create features for the layer
        $features = [
            [
                'layer_id' => $layerId,
                'geometry' => DB::raw("ST_GeomFromText('POINT(8.611 41.145)', 4326)"),
                'properties' => json_encode(['type' => 'point', 'name' => 'Point Feature']),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ],
            [
                'layer_id' => $layerId,
                'geometry' => DB::raw("ST_GeomFromText('LINESTRING(8.6 41.14, 8.61 41.15, 8.62 41.14)', 4326)"),
                'properties' => json_encode(['type' => 'line', 'name' => 'Line Feature']),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ],
            [
                'layer_id' => $layerId,
                'geometry' => DB::raw("ST_GeomFromText('POLYGON((8.6 41.14, 8.61 41.15, 8.62 41.14, 8.6 41.14))', 4326)"),
                'properties' => json_encode(['type' => 'polygon', 'name' => 'Polygon Feature']),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ],
        ];

        DB::table('features')->insert($features);
    }
}
