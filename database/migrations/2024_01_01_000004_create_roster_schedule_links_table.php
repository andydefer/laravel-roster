<?php

declare(strict_types=1);

namespace Roster\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for creating schedule link relationships table.
 *
 * This table enables polymorphic many-to-many relationships between schedules
 * and any other model, with optional metadata storage.
 */
return new class extends Migration
{
    /**
     * Run the migrations to create schedule links table.
     *
     * Creates a polymorphic pivot table for attaching any model type to schedules.
     */
    public function up(): void
    {
        Schema::create('roster_schedule_links', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('schedule_id')
                ->constrained('roster_schedules')
                ->onDelete('cascade');

            $table->unsignedBigInteger('linkable_id');
            $table->string('linkable_type');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('schedule_id');
            $table->index(['linkable_id', 'linkable_type']);
            $table->index('linkable_type');

            $table->unique(
                ['schedule_id', 'linkable_id', 'linkable_type'],
                'roster_schedule_links_unique'
            );
        });
    }

    /**
     * Reverse the migrations by dropping schedule links table.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_schedule_links');
    }
};
