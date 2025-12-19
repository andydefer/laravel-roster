<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_availabilities', function (Blueprint $table): void {
            $table->id();

            $table->morphs('schedulable'); // <-- Déjà crée l'index automatiquement
            $table->string('type')->comment('Type of availability (e.g., consultation, service)');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days')->comment('Recurring days of the week (e.g., ["monday","wednesday"])');

            $table->date('start_date')->nullable()->comment('Start date of the availability period');
            $table->date('end_date')->nullable()->comment('End date of the availability period');

            $table->timestamps();

            // Indexes
            // $table->index(['schedulable_type', 'schedulable_id']); // <-- ENLEVER CETTE LIGNE (en double)
            $table->index('type');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_availabilities');
    }
};
