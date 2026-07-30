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
        Schema::create('visitor_consents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visitor_id')
                ->constrained('visitors')
                ->cascadeOnDelete();

            $table->string('privacy_notice_version', 30);
            $table->string('terms_version', 30);

            $table->timestamp('privacy_accepted_at');
            $table->timestamp('terms_accepted_at');

            $table->boolean('marketing_consent')
                ->default(false);

            $table->timestamp('marketing_consent_at')
                ->nullable();

            $table->ipAddress('ip_address')
                ->nullable();

            $table->string('mac_address', 17)
                ->nullable();

            $table->string('user_agent', 500)
                ->nullable();

            $table->string('source', 50)
                ->default('captive_portal');

            $table->timestamps();

            $table->index([
                'visitor_id',
                'created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_consents');
    }
};
