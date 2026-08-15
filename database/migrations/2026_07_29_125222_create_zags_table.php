<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zags', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);                 // «Дворец бракосочетания №1», «ЗАГС Адмиралтейского района»
            $table->text('address')->nullable();        // адрес
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');

            // Координаты для расчёта расстояний
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Индексы для быстрого поиска
            $table->index(['city_id']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zags');
    }
};
