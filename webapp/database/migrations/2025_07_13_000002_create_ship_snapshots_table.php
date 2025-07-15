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
        Schema::create('ship_snapshots', function (Blueprint $table) {
            $table->id();

            // Foreign key to the current ship (reference)
            $table->foreignId('ship_id')->constrained('ships')->onDelete('cascade');

            // Optional redundancy: allows querying by MMSI directly
            $table->bigInteger('mmsi')->nullable()->index();

            // Timestamp representing when the snapshot was taken
            $table->timestamp('snapshot_time');

            // JSONB field storing the full vessel record (name, type, dimensions, etc.)
            $table->jsonb('properties');

            // Optional audit field
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            // Timestamps for creation and update
            $table->timestamps();

            // Useful indexes
            $table->index('snapshot_time');
            $table->index(['ship_id', 'snapshot_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_snapshots');
    }
};
