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
        Schema::create('access_attempts', function (Blueprint $table) {
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
     * Se conserva el username como copia histórica, incluso si
     * el usuario no existe o posteriormente es eliminado.
     */
            $table->string('username', 100);

            $table->ipAddress('ip_address')->nullable();
            $table->string('mac_address', 17)->nullable();

            $table->string('result', 20);
            $table->string('reason', 100)->nullable();
            $table->string('source', 30)->default('radius');

            /*
     * Permitirá guardar atributos adicionales recibidos
     * desde OPNsense o FreeRADIUS.
     */
            $table->json('metadata')->nullable();

            $table->timestamp('attempted_at')->useCurrent();

            $table->timestamps();

            $table->index(['result', 'attempted_at']);
            $table->index(['username', 'attempted_at']);
            $table->index(['business_id', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_attempts');
    }
};
