<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_address_objs', function (Blueprint $table) {
            $table->id();
            $table->string('address', 255)->nullable(false);
            $table->decimal('latitude', 10, 8)->nullable(false); // до 8 знаков после запятой
            $table->decimal('longitude', 11, 8)->nullable(false); // до 8 знаков после запятой
            $table->unsignedBigInteger('subj_id')->nullable(false);

            // Индексы для ускорения поиска
            $table->index('subj_id');
            $table->index(['latitude', 'longitude']);

            $table->timestamps();

            // Внешний ключ на таблицу объектов (если есть)
            $table->foreign('subj_id')
                ->references('id')
                ->on('subjs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_address_objs');
    }
};
