<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->morphs('schedulable'); // schedulable_type + schedulable_id
            $table->string('type'); // ex: consultation, culte
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days'); // ["monday","tuesday"]
            $table->date('start_date')->nullable(); // période de début
            $table->date('end_date')->nullable();   // période de fin
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('availabilities');
    }
};
