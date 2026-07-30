<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'interest_area_visitor',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('visitor_id')
                    ->constrained('visitors')
                    ->cascadeOnDelete();

                $table->foreignId('interest_area_id')
                    ->constrained('interest_areas')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique([
                    'visitor_id',
                    'interest_area_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_area_visitor');
    }
};
