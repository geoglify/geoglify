<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table first
        DB::table('cargo_categories')->truncate();

        // Cargo categories
        $categories = [
            ["id" => 1, "name" => "No Cargo", "color" => "#8C8C8C", "priority" => 0],
            ["id" => 2, "name" => "WIG (Wing in Ground)", "color" => "#8C8C8C", "priority" => 0],
            ["id" => 3, "name" => "Fishing and Towing", "color" => "#00B159", "priority" => 1],
            ["id" => 4, "name" => "Special Operations", "color" => "#D11141", "priority" => 10],
            ["id" => 5, "name" => "Sailing and Pleasure", "color" => "#FFC425", "priority" => 3],
            ["id" => 6, "name" => "High-Speed Craft", "color" => "#8C8C8C", "priority" => 0],
            ["id" => 7, "name" => "Support Vessels", "color" => "#D11141", "priority" => 10],
            ["id" => 8, "name" => "Passenger Vessels", "color" => "#FF4081", "priority" => 5],
            ["id" => 9, "name" => "Cargo Vessels", "color" => "#F37735", "priority" => 4],
            ["id" => 10, "name" => "Tanker Vessels", "color" => "#546E7A", "priority" => 2],
            ["id" => 11, "name" => "Other Vessels", "color" => "#8C8C8C", "priority" => 0],
        ];

        DB::table('cargo_categories')->insert($categories);
    }
}
