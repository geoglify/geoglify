<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds a geometry column and spatial index to ship_historical_positions table
     */
    public function up()
    {
        Schema::table('ship_historical_positions', function (Blueprint $table) {
            // Add geometry column (Point type with SRID 4326 for WGS84)
            $table->geometry('geom', 'POINT', 4326)->nullable()->after('longitude');
            
            // Add spatial index for better query performance
            $table->spatialIndex('geom');
        });

        // Update existing records to populate the geom column
        DB::statement("
            UPDATE ship_historical_positions 
            SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
            WHERE longitude IS NOT NULL AND latitude IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     * Removes the geometry column and spatial index
     */
    public function down()
    {
        Schema::table('ship_historical_positions', function (Blueprint $table) {
            // First drop the spatial index
            $table->dropSpatialIndex(['geom']);
            
            // Then drop the geometry column
            $table->dropColumn('geom');
        });
    }
};
