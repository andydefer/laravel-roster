<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\DaysOfWeek;

/**
 * Creates the roster_availabilities table for storing recurring availability patterns.
 *
 * This table defines when a schedulable resource is available, including
 * time slots, days of week, and date ranges for the availability period.
 */
return new class extends Migration
{
    /**
     * Creates the roster_availabilities table structure.
     */
    public function up(): void
    {
        Schema::create('roster_availabilities', function (Blueprint $table): void {
            $table->id();
            $table->morphs('schedulable');

            $table->string('type')->comment('Type of availability (e.g., consultation, service)');
            $table->time('daily_start')->comment('Daily start time of the availability slot');
            $table->time('daily_end')->comment('Daily end time of the availability slot');

            // Dans la migration roster_availabilities
            $table->json('days')
                ->default(json_encode(DaysOfWeek::values())) // Tous les jours par défaut
                ->comment('Recurring days of the week (e.g., ["monday","wednesday"])');

            $table->date('validity_start')->nullable()->comment('Start date of the availability validity period');
            $table->date('validity_end')->nullable()->comment('End date of the availability validity period');

            $table->timestamps();

            $table->index('type');
            $table->index(['validity_start', 'validity_end']);
            $table->index(['daily_start', 'daily_end']);
        });
    }

    /**
     * Drops the roster_availabilities table.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_availabilities');
    }
};
