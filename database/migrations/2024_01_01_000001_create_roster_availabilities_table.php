<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days')->comment('Recurring days of the week (e.g., ["monday","wednesday"])');

            $table->date('start_date')->nullable()->comment('Start date of the availability period');
            $table->date('end_date')->nullable()->comment('End date of the availability period');

            $table->timestamps();

            $table->index('type');
            $table->index(['start_date', 'end_date']);
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
