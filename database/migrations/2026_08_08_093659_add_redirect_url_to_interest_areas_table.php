<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'interest_areas',
            function (Blueprint $table): void {
                $table
                    ->string('redirect_url', 2048)
                    ->nullable()
                    ->after('description');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'interest_areas',
            function (Blueprint $table): void {
                $table->dropColumn('redirect_url');
            }
        );
    }
};
