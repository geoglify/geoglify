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
        // Create the function used by the trigger
        DB::statement("
            CREATE OR REPLACE FUNCTION update_geom_from_lat_lon()
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

        // Create the trigger to update 'geom' before INSERT or UPDATE
        DB::statement("
            CREATE TRIGGER trg_update_geom
            BEFORE INSERT OR UPDATE ON ship_realtime_positions
            FOR EACH ROW
            EXECUTE FUNCTION update_geom_from_lat_lon();
        ");
        
        // Update existing records to populate the geom column
        DB::statement("
            UPDATE ship_realtime_positions 
            SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
            WHERE longitude IS NOT NULL AND latitude IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the trigger and the associated function
        DB::statement("DROP TRIGGER IF EXISTS trg_update_geom ON ship_realtime_positions;");
        DB::statement("DROP FUNCTION IF EXISTS update_geom_from_lat_lon();");
    }
};
