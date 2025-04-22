<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cargo_types')->truncate();

        $cargoTypes = [
            // No Cargo
            ["id" => 1, "code" => 0, "name" => "No cargo type specified", "cargo_category_id" => 1],

            // WIG
            ["id" => 2, "code" => 20, "name" => "Wing in Ground", "cargo_category_id" => 2],
            ["id" => 3, "code" => 29, "name" => "Search and Rescue Aircraft", "cargo_category_id" => 2],

            // Fishing and Towing
            ["id" => 4, "code" => 30, "name" => "Fishing Vessel", "cargo_category_id" => 3],
            ["id" => 5, "code" => 31, "name" => "Towing Vessel", "cargo_category_id" => 3],
            ["id" => 6, "code" => 32, "name" => "Towing: length exceeds 200m or breadth exceeds 25m", "cargo_category_id" => 3],

            // Dredger and Operations
            ["id" => 7, "code" => 33, "name" => "Dredger Vessel", "cargo_category_id" => 4],
            ["id" => 8, "code" => 34, "name" => "Diving Operations", "cargo_category_id" => 4],
            ["id" => 9, "code" => 35, "name" => "Military Operations", "cargo_category_id" => 4],

            // Sailing and Pleasure
            ["id" => 10, "code" => 36, "name" => "Sailing Vessel", "cargo_category_id" => 5],
            ["id" => 11, "code" => 37, "name" => "Pleasure Craft Vessel", "cargo_category_id" => 5],

            // High-Speed Craft
            ["id" => 12, "code" => 40, "name" => "High-Speed Craft", "cargo_category_id" => 6],

            // Support Vessels
            ["id" => 13, "code" => 50, "name" => "Pilot Vessel", "cargo_category_id" => 7],
            ["id" => 14, "code" => 51, "name" => "Search and Rescue Vessel", "cargo_category_id" => 7],
            ["id" => 15, "code" => 52, "name" => "Tug Vessel", "cargo_category_id" => 7],
            ["id" => 16, "code" => 53, "name" => "Port Tender Vessel", "cargo_category_id" => 7],
            ["id" => 17, "code" => 54, "name" => "Anti-Pollution Vessel", "cargo_category_id" => 7],
            ["id" => 18, "code" => 55, "name" => "Law Enforcement Vessel", "cargo_category_id" => 7],
            ["id" => 19, "code" => 56, "name" => "Local Vessel", "cargo_category_id" => 7],
            ["id" => 20, "code" => 58, "name" => "Medical Transport Vessel", "cargo_category_id" => 7],
            ["id" => 21, "code" => 59, "name" => "Noncombatant Vessel", "cargo_category_id" => 7],

            // Passenger Vessels
            ["id" => 22, "code" => 60, "name" => "Passenger: All Ships", "cargo_category_id" => 8],
            ["id" => 23, "code" => 61, "name" => "Passenger: Hazardous category A", "cargo_category_id" => 8],
            ["id" => 24, "code" => 62, "name" => "Passenger: Hazardous category B", "cargo_category_id" => 8],
            ["id" => 25, "code" => 63, "name" => "Passenger: Hazardous category C", "cargo_category_id" => 8],
            ["id" => 26, "code" => 64, "name" => "Passenger: Hazardous category D", "cargo_category_id" => 8],
            ["id" => 27, "code" => 65, "name" => "Passenger: Reserved for Future Use", "cargo_category_id" => 8],
            ["id" => 28, "code" => 66, "name" => "Passenger: Reserved for Future Use", "cargo_category_id" => 8],
            ["id" => 29, "code" => 67, "name" => "Passenger: Reserved for Future Use", "cargo_category_id" => 8],
            ["id" => 30, "code" => 68, "name" => "Passenger: Reserved for Future Use", "cargo_category_id" => 8],

            // Cargo Vessels
            ["id" => 31, "code" => 70, "name" => "Cargo: All Ships", "cargo_category_id" => 9],
            ["id" => 32, "code" => 71, "name" => "Cargo: Hazardous category A", "cargo_category_id" => 9],
            ["id" => 33, "code" => 72, "name" => "Cargo: Hazardous category B", "cargo_category_id" => 9],
            ["id" => 34, "code" => 73, "name" => "Cargo: Hazardous category C", "cargo_category_id" => 9],
            ["id" => 35, "code" => 74, "name" => "Cargo: Hazardous category D", "cargo_category_id" => 9],

            // Tanker Vessels
            ["id" => 36, "code" => 80, "name" => "Tanker: All Ships", "cargo_category_id" => 10],
            ["id" => 37, "code" => 81, "name" => "Tanker: Hazardous category A", "cargo_category_id" => 10],
            ["id" => 38, "code" => 82, "name" => "Tanker: Hazardous category B", "cargo_category_id" => 10],
            ["id" => 39, "code" => 83, "name" => "Tanker: Hazardous category C", "cargo_category_id" => 10],
            ["id" => 40, "code" => 84, "name" => "Tanker: Hazardous category D", "cargo_category_id" => 10],

            // Other Vessels
            ["id" => 41, "code" => 90, "name" => "Other: All Ships", "cargo_category_id" => 11],
            ["id" => 42, "code" => 91, "name" => "Other: Hazardous category A", "cargo_category_id" => 11],
            ["id" => 43, "code" => 92, "name" => "Other: Hazardous category B", "cargo_category_id" => 11],
            ["id" => 44, "code" => 93, "name" => "Other: Hazardous category C", "cargo_category_id" => 11],
            ["id" => 45, "code" => 94, "name" => "Other: Hazardous category D", "cargo_category_id" => 11]
        ];

        DB::table('cargo_types')->insert($cargoTypes);
    }
}
