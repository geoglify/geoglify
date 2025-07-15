<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->id();

            // MMSI (Maritime Mobile Service Identity) - unique ship identifier
            $table->bigInteger('mmsi')->unique();

            // IMO number (optional) - International Maritime Organization ID
            $table->bigInteger('imo')->nullable()->index();

            // Ship name - default to 'Unknown' if not provided
            $table->string('name')->nullable()->default('Unknown');

            // AIS type - identifies the type of ship (e.g., cargo, tanker, etc.)
            $table->integer('ais_type')->nullable();

            // Dimensions relative to the position of the antenna/GPS (A: bow, B: stern, C: port, D: starboard)
            $table->integer('dim_a')->nullable();
            $table->integer('dim_b')->nullable();
            $table->integer('dim_c')->nullable();
            $table->integer('dim_d')->nullable();

            // Cargo type (according to AIS codes)
            $table->integer('cargo')->nullable();

            // Physical dimensions
            $table->decimal('length', 8, 2)->nullable(); // in meters
            $table->decimal('width', 8, 2)->nullable();  // in meters

            // Audit fields for accountability
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            // Timestamps: created_at and updated_at
            $table->timestamps();

            // Soft deletes: adds deleted_at column
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ships');
    }
};