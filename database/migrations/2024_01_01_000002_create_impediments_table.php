<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the `impediments` table.
 *
 * This table stores temporary exceptions that block availability, even if a schedulable
 * entity would normally be available. Examples: illness, training, holidays.
 *
 * Each impediment is linked to an availability rule and defines a time range that
 * should not be booked.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('impediments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('availability_id')
                ->constrained('availabilities')
                ->onDelete('cascade');

            $table->morphs('schedulable');

            $table->string('reason')->nullable()->comment('Optional explanation for the impediment');
            $table->dateTime('start_datetime')->comment('Start of the blocked period');
            $table->dateTime('end_datetime')->comment('End of the blocked period');
            $table->json('metadata')->nullable()->comment('Optional additional data');
            $table->timestamps();

            // Prevent duplicate impediments for the exact same period
            $table->unique(
                ['availability_id', 'start_datetime', 'end_datetime'],
                'impediments_unique_time_slot'
            );

            $table->index(['availability_id', 'start_datetime']);
            $table->index(['availability_id', 'end_datetime']);
            $table->index(['start_datetime', 'end_datetime']);
        });

        // Add database-specific constraints
        $this->addDatabaseSpecificConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impediments');
    }

    /**
     * Add database-specific constraints.
     */
    private function addDatabaseSpecificConstraints(): void
    {
        $connection = config('database.default');

        switch ($connection) {
            case 'mysql':
                $this->addMysqlConstraints();
                break;
            case 'pgsql':
                $this->addPgsqlConstraints();
                break;
            case 'sqlite':
                $this->addSqliteConstraints();
                break;
        }
    }

    /**
     * Add MySQL specific constraints.
     */
    private function addMysqlConstraints(): void
    {
        if (config('roster.database.check_constraints', true)) {
            DB::statement('
                ALTER TABLE impediments
                ADD CONSTRAINT impediments_valid_dates
                CHECK (end_datetime > start_datetime)
            ');
        }
    }

    /**
     * Add PostgreSQL specific constraints.
     */
    private function addPgsqlConstraints(): void
    {
        if (config('roster.database.use_json_constraints', true)) {
            DB::statement('
                ALTER TABLE impediments
                ADD CONSTRAINT impediments_no_overlap
                EXCLUDE USING gist (
                    availability_id WITH =,
                    tsrange(start_datetime, end_datetime) WITH &&
                )
            ');
        }
    }

    /**
     * Add SQLite specific constraints.
     */
    private function addSqliteConstraints(): void
    {
        // SQLite doesn't support check constraints in the same way
        // We'll rely on application-level validation
    }
};
