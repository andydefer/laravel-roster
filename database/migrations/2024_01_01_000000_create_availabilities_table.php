<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `availabilities` table for storing recurring availability rules.
 *
 * This table defines theoretical time periods when a schedulable entity
 * (doctor, room, team, equipment) can be booked, including recurring days
 * and date ranges. Represents "ideal working hours" or "default availability"
 * that can be constrained by schedules or impediments.
 */
return new class extends Migration
{
    /**
     * Creates the availabilities table structure.
     */
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table): void {
            $table->id();

            $table->morphs('schedulable');
            $table->string('type')->comment('Type of availability (e.g., consultation, service)');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days')->comment('Recurring days of the week (e.g., ["monday","wednesday"])');

            $table->date('start_date')->nullable()->comment('Start date of the availability period');
            $table->date('end_date')->nullable()->comment('End date of the availability period');

            $table->timestamps();
        });
    }

    /**
     * Drops the availabilities table.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
