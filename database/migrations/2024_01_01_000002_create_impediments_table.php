<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the `impediments` table.
 *
 * This table stores temporary exceptions that block availability, even if a schedulable
 * entity would normally be available. Examples: illness, training, holidays.
 *
 * Each impediment is linked to an availability rule and defines a time range that
 * should not be booked.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('impediments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('availability_id')
                ->constrained('availabilities')
                ->onDelete('cascade');

            $table->string('reason')->nullable()->comment('Optional explanation for the impediment');
            $table->dateTime('start_datetime')->comment('Start of the blocked period');
            $table->dateTime('end_datetime')->comment('End of the blocked period');
            $table->json('metadata')->nullable()->comment('Optional additional data');
            $table->timestamps();

            // Prevent duplicate impediments for the exact same period
            $table->unique(
                ['availability_id', 'start_datetime', 'end_datetime'],
                'impediments_unique_time_slot'
            );

            $table->index(['availability_id', 'start_datetime']);
            $table->index(['availability_id', 'end_datetime']);
            $table->index(['start_datetime', 'end_datetime']);

            // Optional: ensure end > start (MySQL only)
            if (config('database.default') === 'mysql') {
                $table->rawIndex(
                    'CHECK(end_datetime > start_datetime)',
                    'impediments_valid_dates'
                );
            }
        });

        // PostgreSQL: Prevent overlapping time ranges per availability
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impediments');
    }
};
