<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number');
            $table->string('code');
            $table->string('iso_code');
            $table->string('region_code')->nullable();
            $table->string('name');

            // Audit Fields
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        // Drop the table
        Schema::dropIfExists('countries');
    }
};
