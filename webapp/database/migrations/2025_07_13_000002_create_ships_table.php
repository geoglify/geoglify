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
            $table->bigInteger('mmsi')->unique();
            $table->bigInteger('imo')->nullable()->index();
            $table->string('name');
            $table->string('call_sign')->nullable();
            $table->integer('ship_type')->nullable();
            $table->integer('dim_a')->nullable();
            $table->integer('dim_b')->nullable();
            $table->integer('dim_c')->nullable();
            $table->integer('dim_d')->nullable();
            $table->jsonb('properties')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
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
