<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('test_schedulables')) {
            Schema::create('test_schedulables', function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('test_schedulables');
    }
};
