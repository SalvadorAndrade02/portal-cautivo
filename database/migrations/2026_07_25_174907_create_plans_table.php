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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('description')->nullable();

            $table->unsignedInteger('download_speed_mbps')->nullable();
            $table->unsignedInteger('upload_speed_mbps')->nullable();

            $table->unsignedInteger('session_timeout_minutes')->default(480);
            $table->unsignedInteger('idle_timeout_minutes')->default(15);
            $table->unsignedInteger('max_devices')->default(1);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
