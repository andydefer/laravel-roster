<?php

declare(strict_types=1);

namespace Roster\Database\Migrations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\DaysOfWeek;

/**
 * Creates the roster_availabilities table for storing recurring availability schedules.
 *
 * This migration sets up a polymorphic table to associate availability schedules
 * with various schedulable entities (e.g., doctors, resources, services).
 * Each record represents a recurring time slot with specific validity period.
 */
return new class extends Migration
{
    /**
     * Creates the roster_availabilities table structure.
     *
     * The table supports:
     * - Polymorphic relationships to schedulable entities
     * - Recurring weekly schedules with specific days
     * - Time-bound validity periods
     * - Efficient querying through optimized indexes
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('roster_availabilities', function (Blueprint $table): void {
            $table->id();

            $table->morphs('schedulable');

            $table->string('type')->comment('Type of availability (e.g., consultation, service)');
            $table->time('daily_start')->comment('Daily start time of the availability slot');
            $table->time('daily_end')->comment('Daily end time of the availability slot');

            $table->json('days')
                ->default(json_encode(DaysOfWeek::values()))
                ->comment('Recurring days of the week (e.g., ["monday","wednesday"])');

            $table->timestamp('validity_start')->comment('Start timestamp of the availability validity period');
            $table->timestamp('validity_end')->comment('End timestamp of the availability validity period');

            $table->timestamps();
            $table->softDeletes();


            $table->index('type');
            $table->index(['validity_start', 'validity_end']);
            $table->index(['daily_start', 'daily_end']);
        });
    }

    /**
     * Drops the roster_availabilities table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_availabilities');
    }
};
