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
        Schema::create('access_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portal_user_id')
                ->nullable()
                ->constrained('portal_users')
                ->nullOnDelete();

            $table->foreignId('business_id')
                ->nullable()
                ->constrained('businesses')
                ->nullOnDelete();

            $table->foreignId('device_id')
                ->nullable()
                ->constrained('devices')
                ->nullOnDelete();

            /*
     * Identificador proporcionado por RADIUS Accounting.
     */
            $table->string('radius_session_id', 191)->unique();

            /*
     * Copia histórica del username utilizado.
     */
            $table->string('username', 100);

            $table->ipAddress('ip_address')->nullable();
            $table->string('mac_address', 17)->nullable();

            $table->ipAddress('nas_ip_address')->nullable();
            $table->string('nas_identifier', 100)->nullable();

            $table->timestamp('started_at');
            $table->timestamp('last_update_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->unsignedBigInteger('duration_seconds')->default(0);
            $table->unsignedBigInteger('input_bytes')->default(0);
            $table->unsignedBigInteger('output_bytes')->default(0);

            $table->string('termination_reason', 100)->nullable();
            $table->string('status', 20)->default('active');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['status', 'started_at']);
            $table->index(['business_id', 'status']);
            $table->index(['username', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_sessions');
    }
};
