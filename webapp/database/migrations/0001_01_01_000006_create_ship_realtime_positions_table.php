<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create the table
        Schema::create('ship_realtime_positions', function (Blueprint $table) {
            
            $table->id();
            $table->integer('ship_id')->unique();
            $table->decimal('cog', 5, 2)->nullable();
            $table->decimal('sog', 5, 2)->nullable();
            $table->integer('hdg')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamp('eta')->nullable();
            $table->string('destination')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            // Foreign Keys
            $table->foreign('ship_id')->references('id')->on('ships')->onDelete('set null');

            // Audit Fields
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        // Drop the table
        Schema::dropIfExists('ship_realtime_positions');
    }
};
