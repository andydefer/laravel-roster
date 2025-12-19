<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\ScheduleStatus;

/**
 * Creates the `roster_schedules` table for storing concrete scheduled events.
 *
 * This table stores actual bookings (appointments, meetings, etc.) linked
 * to availability rules, including their statuses and metadata.
 */
return new class extends Migration
{
    /**
     * Creates the roster_schedules table structure.
     */
    public function up(): void
    {
        Schema::create('roster_schedules', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('availability_id')
                ->constrained('roster_availabilities')
                ->onDelete('cascade');

            $table->string('title')->comment('Title of the scheduled event');
            $table->text('description')->nullable()->comment('Optional description');
            $table->dateTime('start_datetime')->comment('Start of the scheduled event');
            $table->dateTime('end_datetime')->comment('End of the scheduled event');
            $table->json('metadata')->nullable()->comment('Optional additional information');

            $table->enum('status', ScheduleStatus::values())
                ->default(ScheduleStatus::AVAILABLE->value)
                ->comment('Current status of the event');

            $table->timestamps();

            // Indexes
            $table->index('availability_id');
            $table->index(['start_datetime', 'end_datetime']);
            $table->index('status');
        });
    }

    /**
     * Drops the roster_schedules table.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_schedules');
    }
};
