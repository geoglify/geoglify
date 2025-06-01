<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->nullable()->constrained('layers')->onDelete('set null')->index('fk_features_layer_id');
            $table->geometry('geometry', 'GEOMETRY', 4326)->comment('Geometry of the feature, using PostGIS GEOMETRY type with SRID 4326');
            $table->jsonb('geojson')->nullable()->comment('GeoJSON representation of the feature');
            $table->jsonb('properties')->nullable()->comment('Properties of the feature in JSON format');

            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->index('fk_features_created_by');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->index('fk_features_updated_by');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null')->index('fk_features_deleted_by');
            
        });

        // Add GiST index
        DB::statement('CREATE INDEX features_geometry_gist ON features USING GIST (geometry);');

        // Trigger to update geojson on insert or update
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_geojson()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.geojson := ST_AsGeoJSON(NEW.geometry)::jsonb;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER update_geojson_trigger
            BEFORE INSERT OR UPDATE ON features
            FOR EACH ROW EXECUTE FUNCTION update_geojson();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
