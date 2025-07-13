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
        Schema::create('ship_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_id')->constrained('ships')->onDelete('cascade');
            $table->bigInteger('mmsi')->nullable()->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('cog', 5, 1)->nullable();
            $table->decimal('sog', 5, 1)->nullable();
            $table->integer('heading')->nullable();
            $table->integer('nav_status')->nullable();
            $table->timestamp('timestamp');
            $table->geometry('geometry', 'POINT', 4326)->nullable();
            $table->jsonb('geojson')->nullable();
            $table->jsonb('properties')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_positions');
    }
};
