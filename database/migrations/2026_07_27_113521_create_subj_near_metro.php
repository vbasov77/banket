<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subj_near_metro', function (Blueprint $table) {
            $table->id();

            // Связь с субъектом (у него есть адрес/координаты)
            $table->unsignedBigInteger('subj_id');
            $table->foreign('subj_id')
                ->references('id')
                ->on('subjs')
                ->onDelete('cascade');

            // Ссылка на станцию метро
            $table->unsignedBigInteger('metro_station_id');
            $table->foreign('metro_station_id')
                ->references('id')
                ->on('metro_stations')
                ->onDelete('cascade');

            $table->decimal('distance_km', 8, 3)->nullable(false);
            $table->smallInteger('rank')->nullable(false); // 1, 2, 3

            // Уникальность пары: один субъект — одна станция
            $table->unique(['subj_id', 'metro_station_id']);
            $table->index('subj_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subj_near_metro');
    }
};
