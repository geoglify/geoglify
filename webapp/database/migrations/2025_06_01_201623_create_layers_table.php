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
        Schema::create('layers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('geometry_type')->default('GEOMETRY')->comment('Type of geometry, e.g., POINT, LINESTRING, POLYGON');
            $table->boolean('is_visible')->default(true)->index();

            // Audit fields
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->index('fk_features_created_by');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->index('fk_features_updated_by');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null')->index('fk_features_deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layers');
    }
};
