<?php

namespace Database\Seeders;

use Database\Seeders\CargoCategoriesSeeder;
use Database\Seeders\CargoTypesSeeder;
use Database\Seeders\CountriesTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CargoCategoriesSeeder::class,
            CargoTypesSeeder::class,
            CountriesTableSeeder::class,
        ]);
    }
}
