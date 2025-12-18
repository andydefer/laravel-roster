<?php

// ==== database/migrations/2024_01_01_000002_create_impediments_table.php ====

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impediments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')
                ->constrained('availabilities')
                ->onDelete('cascade');
            $table->string('reason')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Contrainte unique pour empêcher les impediments identiques exactement au même moment
            $table->unique(
                ['availability_id', 'start_datetime', 'end_datetime'],
                'impediments_unique_time_slot'
            );

            // Index pour optimiser les recherches de chevauchement
            // (Logique d'évitement de chevauchement se fera dans le service)
            $table->index(['availability_id', 'start_datetime']);
            $table->index(['availability_id', 'end_datetime']);
            $table->index(['start_datetime', 'end_datetime']);

            // Vérification que end_datetime > start_datetime
            // (Cette contrainte dépend du SGBD)
            if (config('database.default') === 'mysql') {
                $table->rawIndex(
                    'CHECK(end_datetime > start_datetime)',
                    'impediments_valid_dates'
                );
            }
        });

        // Pour PostgreSQL uniquement : contrainte EXCLUDE pour empêcher les chevauchements
        if (config('database.default') === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('
                ALTER TABLE impediments
                ADD CONSTRAINT impediments_no_overlap
                EXCLUDE USING gist (
                    availability_id WITH =,
                    tsrange(start_datetime, end_datetime) WITH &&
                )
            ');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('impediments');
    }
};
