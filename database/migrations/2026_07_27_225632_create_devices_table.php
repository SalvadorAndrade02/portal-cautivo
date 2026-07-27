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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses');

            $table->foreignId('portal_user_id')
                ->nullable()
                ->constrained('portal_users')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->string('device_type', 30)->default('other');

            $table->string('mac_address', 17)->unique();
            $table->ipAddress('last_ip_address')->nullable();

            $table->boolean('authorized')->default(false);
            $table->boolean('blocked')->default(false);
            $table->boolean('bypass_portal')->default(false);

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->index(['business_id', 'blocked']);
            $table->index(['business_id', 'authorized']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
