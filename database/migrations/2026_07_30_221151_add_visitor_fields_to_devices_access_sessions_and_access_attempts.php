<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Los dispositivos de visitantes no pertenecen
         * necesariamente a un local.
         */
        Schema::table('devices', function (Blueprint $table): void {
            $table->dropForeign(['business_id']);
        });

        Schema::table('devices', function (Blueprint $table): void {
            $table->unsignedBigInteger('business_id')
                ->nullable()
                ->change();

            $table->foreign('business_id')
                ->references('id')
                ->on('businesses')
                ->nullOnDelete();

            $table->foreignId('visitor_id')
                ->nullable()
                ->after('portal_user_id')
                ->constrained('visitors')
                ->nullOnDelete();
        });

        Schema::table(
            'access_sessions',
            function (Blueprint $table): void {
                $table->foreignId('visitor_id')
                    ->nullable()
                    ->after('portal_user_id')
                    ->constrained('visitors')
                    ->nullOnDelete();

                $table->string('access_type', 30)
                    ->default('business_user')
                    ->after('visitor_id')
                    ->index();
            }
        );

        Schema::table(
            'access_attempts',
            function (Blueprint $table): void {
                $table->foreignId('visitor_id')
                    ->nullable()
                    ->after('portal_user_id')
                    ->constrained('visitors')
                    ->nullOnDelete();

                $table->string('access_type', 30)
                    ->default('business_user')
                    ->after('visitor_id')
                    ->index();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'access_attempts',
            function (Blueprint $table): void {
                $table->dropForeign(['visitor_id']);
                $table->dropColumn([
                    'visitor_id',
                    'access_type',
                ]);
            }
        );

        Schema::table(
            'access_sessions',
            function (Blueprint $table): void {
                $table->dropForeign(['visitor_id']);
                $table->dropColumn([
                    'visitor_id',
                    'access_type',
                ]);
            }
        );

        Schema::table('devices', function (Blueprint $table): void {
            $table->dropForeign(['visitor_id']);
            $table->dropForeign(['business_id']);

            $table->dropColumn('visitor_id');

            $table->unsignedBigInteger('business_id')
                ->nullable(false)
                ->change();

            $table->foreign('business_id')
                ->references('id')
                ->on('businesses');
        });
    }
};
