<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create the ships table
        Schema::create('ships', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedInteger('mmsi')->unique();
            $table->string('name')->nullable();
            $table->string('callsign')->nullable();
            $table->string('imo')->nullable();
            $table->integer('dim_a')->nullable();
            $table->integer('dim_b')->nullable();
            $table->integer('dim_c')->nullable();
            $table->integer('dim_d')->nullable();
            $table->integer('cargo_type_id')->nullable();
            $table->decimal('draught', 5, 2)->nullable();

            // Audit Fields
            $table->timestamps();
            $table->softDeletes();

            // Adding foreign keys directly
            $table->foreign('cargo_type_id')->references('id')->on('cargo_types')->onDelete('set null');

            // Indexes
            $table->index('mmsi');
            $table->index('name');
            $table->index('callsign');
            $table->index('imo');
        });
    }

    public function down()
    {
        // Drop the ships table
        Schema::dropIfExists('ships');
    }
};
