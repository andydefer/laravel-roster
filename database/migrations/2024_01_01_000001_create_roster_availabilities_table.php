<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Roster\Enums\DaysOfWeek;

return new class extends Migration
{
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

            // CHANGEMENT ICI : date → datetime (ou timestamp)
            $table->timestamp('validity_start')->comment('Start timestamp of the availability validity period');
            $table->timestamp('validity_end')->comment('End timestamp of the availability validity period');

            $table->timestamps();

            $table->index('type');
            $table->index(['validity_start', 'validity_end']);
            $table->index(['daily_start', 'daily_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_availabilities');
    }
};
