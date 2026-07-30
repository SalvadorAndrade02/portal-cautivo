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
        Schema::create(
            'visitor_access_tokens',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('visitor_id')
                    ->constrained('visitors')
                    ->cascadeOnDelete();

                $table->foreignId('device_id')
                    ->nullable()
                    ->constrained('devices')
                    ->nullOnDelete();

                $table->string('access_username', 100)
                    ->unique();

                $table->string('token_hash', 255);

                $table->timestamp('expires_at');

                $table->timestamp('used_at')
                    ->nullable();

                $table->timestamp('last_used_at')
                    ->nullable();

                $table->timestamp('revoked_at')
                    ->nullable();

                $table->string('status', 20)
                    ->default('active')
                    ->index();

                $table->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'visitor_id',
                    'status',
                ]);

                $table->index([
                    'expires_at',
                    'status',
                ]);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_access_tokens');
    }
};
