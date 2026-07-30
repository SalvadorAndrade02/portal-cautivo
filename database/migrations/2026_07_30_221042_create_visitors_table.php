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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();

            $table->string('full_name', 150);
            $table->string('phone', 30)->index();
            $table->string('email', 150)->index();

            $table->string('status', 20)
                ->default('active')
                ->index();

            $table->timestamp('registered_at')
                ->useCurrent();

            $table->timestamp('last_access_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
