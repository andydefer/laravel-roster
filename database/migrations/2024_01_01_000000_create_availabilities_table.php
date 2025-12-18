<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the `availabilities` table.
 *
 * This table stores recurring availability rules for any schedulable entity
 * (e.g., doctor, room, team, or equipment). It defines the theoretical
 * periods when a schedulable can be booked, including recurring days and
e ranges.
 * * optional dat
 * Conceptually, this represents "ideal working hours" or "default availability"
 * that can later be constrained by schedules or impediments.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
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
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
