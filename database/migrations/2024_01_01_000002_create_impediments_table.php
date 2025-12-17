<?php
// ==== database/migrations/2024_01_01_000002_create_impediments_table.php ====

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impediments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')
                ->constrained('availabilities')
                ->onDelete('cascade');
            $table->string('reason')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Index pour les recherches de chevauchement

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impediments');
    }
};
