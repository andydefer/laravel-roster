<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\ScheduleStatus;

/**
 * Creates the `schedules` table for storing concrete scheduled events.
 *
 * This table stores actual bookings (appointments, meetings, etc.) linked
 * to availability rules, including their statuses and metadata.
 */
return new class extends Migration
{
    /**
     * Creates the schedules table structure.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('availability_id')
                ->constrained('availabilities')
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
        });
    }

    /**
     * Drops the schedules table.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
