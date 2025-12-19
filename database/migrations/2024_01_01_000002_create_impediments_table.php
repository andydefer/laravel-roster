<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `impediments` table for storing temporary availability exceptions.
 *
 * This table stores blocking periods that override normal availability rules.
 * Examples include illness, training, holidays, or maintenance periods.
 */
return new class extends Migration
{
    /**
     * Creates the impediments table structure with database-specific constraints.
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

            $table->unique(
                ['availability_id', 'start_datetime', 'end_datetime'],
                'impediments_unique_time_slot'
            );

            $table->index(['availability_id', 'start_datetime']);
            $table->index(['availability_id', 'end_datetime']);
            $table->index(['start_datetime', 'end_datetime']);
        });

        $this->addDatabaseSpecificConstraints();
    }

    /**
     * Drops the impediments table.
     */
    public function down(): void
    {
        Schema::dropIfExists('impediments');
    }

    /**
     * Adds database-specific constraints for data integrity.
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
                // SQLite doesn't support advanced check constraints
                break;
        }
    }

    /**
     * Adds MySQL-specific date validation constraints.
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
     * Adds PostgreSQL-specific overlap prevention constraints.
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
};
