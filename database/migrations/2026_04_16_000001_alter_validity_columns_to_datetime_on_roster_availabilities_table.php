<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_availabilities', function (Blueprint $table) {
            $table->dateTime('validity_start')->change();
            $table->dateTime('validity_end')->change();
        });
    }

    public function down(): void
    {
        Schema::table('roster_availabilities', function (Blueprint $table) {
            $table->timestamp('validity_start')->change();
            $table->timestamp('validity_end')->change();
        });
    }
};