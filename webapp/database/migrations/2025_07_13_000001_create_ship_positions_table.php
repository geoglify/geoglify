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
        Schema::create('ship_positions', function (Blueprint $table) {
            $table->id();

            // Reference to the main ship entry
            $table->foreignId('ship_id')->constrained('ships')->onDelete('cascade');

            // Redundant identifiers for quick access (optional, improves performance for some queries)
            $table->bigInteger('mmsi')->nullable()->index();
            $table->bigInteger('imo')->nullable()->index();

            // Ship name at the time of position report (may differ from current name)
            $table->string('name')->nullable()->default('Unknown');

            // Geospatial location (WGS 84)
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Course Over Ground, Speed Over Ground, Heading
            $table->decimal('cog', 5, 1)->nullable(); // in degrees
            $table->decimal('sog', 5, 1)->nullable(); // in knots
            $table->integer('heading')->nullable();  // in degrees

            // Navigation status (according to AIS)
            $table->integer('nav_status')->nullable();

            // Rate of Turn (ROT) in degrees/min
            $table->decimal('rot', 8, 3)->nullable();

            // AIS repeat indicator (used in AIS message redundancy)
            $table->integer('repeat')->nullable();

            // Communication metadata
            $table->string('channel')->nullable(); // VHF channel used
            $table->integer('utc')->nullable();    // UTC second

            // Optional voyage data
            $table->string('smi')->nullable();         // Ship Management Information (optional)
            $table->string('destination')->nullable(); // Destination set by crew
            $table->decimal('draught', 5, 2)->nullable(); // Draught in meters

            // Ship characteristics snapshot at that time
            $table->integer('ais_type')->nullable();
            $table->integer('dim_a')->nullable();
            $table->integer('dim_b')->nullable();
            $table->integer('dim_c')->nullable();
            $table->integer('dim_d')->nullable();
            $table->integer('cargo')->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();

            // Timestamp of the AIS message (not the DB timestamp)
            $table->timestamp('timestamp');

            // Geometry column for spatial indexing and queries (PostGIS)
            $table->geometry('geometry', 'POINT', 4326)->nullable();

            // Audit information
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            // Laravel timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
        });

        // PostGIS index for spatial queries (GIST index)
        DB::statement('CREATE INDEX ship_positions_geometry_idx ON ship_positions USING GIST (geometry)');

        // Additional indexes for efficient time-based and vessel-based queries
        DB::statement('CREATE INDEX ship_positions_mmsi_idx ON ship_positions (mmsi)');
        DB::statement('CREATE INDEX ship_positions_timestamp_idx ON ship_positions (timestamp)');
        DB::statement('CREATE INDEX ship_positions_ship_id_idx ON ship_positions (ship_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_positions');
    }
};
