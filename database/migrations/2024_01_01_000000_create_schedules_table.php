<?php

// ==== database/migrations/2024_01_01_000001_create_schedules_table.php ====

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\ScheduleStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            // Référence directe à Availability (plus de morph)
            $table->foreignId('availability_id')
                ->constrained('availabilities')
                ->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->json('metadata')->nullable();

            $table->enum('status', ScheduleStatus::values())
                ->default(ScheduleStatus::AVAILABLE->value);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
