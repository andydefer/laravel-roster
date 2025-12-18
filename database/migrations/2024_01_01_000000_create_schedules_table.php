<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\ScheduleStatus;

/**
 * Migration to create the `schedules` table.
 *
 * This table stores concrete scheduled events linked to an availability rule.
 * It represents actual bookings (appointments, meetings, etc.) and their statuses.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
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
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
