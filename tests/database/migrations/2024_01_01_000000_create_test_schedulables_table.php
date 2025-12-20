<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration class for creating the `test_schedulables` table.
 *
 * Handles the creation and deletion of the table used for testing schedulable entities.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `test_schedulables` table if it does not exist.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('test_schedulables')) {
            Schema::create('test_schedulables', function (Blueprint $table): void {
                $table->id();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `test_schedulables` table if it exists.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('test_schedulables');
    }
};
