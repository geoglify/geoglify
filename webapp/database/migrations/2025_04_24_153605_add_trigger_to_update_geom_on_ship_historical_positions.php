<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create the function for ship_historical_positions
        DB::statement("
            CREATE OR REPLACE FUNCTION update_geom_from_lat_lon_historical()
            RETURNS trigger AS $$
            BEGIN
                IF NEW.longitude IS NOT NULL AND NEW.latitude IS NOT NULL THEN
                    NEW.geom := ST_SetSRID(ST_MakePoint(NEW.longitude, NEW.latitude), 4326);
                ELSE
                    NEW.geom := NULL;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Create the trigger
        DB::statement("
            CREATE TRIGGER trg_update_geom_historical
            BEFORE INSERT OR UPDATE ON ship_historical_positions
            FOR EACH ROW
            EXECUTE FUNCTION update_geom_from_lat_lon_historical();
        ");
        
        // Update existing records to populate the geom column
        DB::statement("
            UPDATE ship_historical_positions 
            SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
            WHERE longitude IS NOT NULL AND latitude IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("DROP TRIGGER IF EXISTS trg_update_geom_historical ON ship_historical_positions;");
        DB::statement("DROP FUNCTION IF EXISTS update_geom_from_lat_lon_historical();");
    }
};
