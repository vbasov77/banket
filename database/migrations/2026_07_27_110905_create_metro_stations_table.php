<?php

// database/migrations/xxxx_xx_xx_create_metro_stations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metro_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('city_id')->default(1); // У тебя city_id уже есть
            $table->decimal('latitude', 10, 8)->nullable(false);
            $table->decimal('longitude', 11, 8)->nullable(false);

            // Индекс по городу и координатам для быстрого поиска
            $table->index(['city_id']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metro_stations');
    }
};
