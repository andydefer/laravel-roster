<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\ScheduleStatus;

/**
 * Migration for creating the roster_schedules table.
 *
 * This table stores concrete scheduled events (appointments, meetings, etc.)
 * linked to availability rules with their statuses and metadata.
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

            $table->morphs('schedulable');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->json('metadata')->nullable();

            $table->enum('status', ScheduleStatus::values())
                ->default(ScheduleStatus::AVAILABLE->value);

            $table->timestamps();

            $table->index('availability_id');
            $table->index(['start_datetime', 'end_datetime']);
            $table->index('status');
        });
    }

    /**
     * Reverts the migration by dropping the roster_schedules table.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_schedules');
    }
};
