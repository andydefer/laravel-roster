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
     */
    public function up(): void
    {
        if (! Schema::hasTable('test_schedulables')) {
            Schema::create('test_schedulables', function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->timestamps();
                $blueprint->string('name')->default('Dr. John Doe');
                $blueprint->string('specialty')->default('cardiology');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `test_schedulables` table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_schedulables');
    }
};
