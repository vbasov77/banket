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
            $table->integer('city_id');
            $table->integer('district_id');
            // Индексы для ускорения поиска
            $table->index('obj_id');
            $table->string('address', 255)->nullable(false);
            $table->decimal('latitude', 10, 8)->nullable(false); // до 8 знаков после запятой
            $table->decimal('longitude', 11, 8)->nullable(false); // до 8 знаков после запятой
            $table->unsignedBigInteger('obj_id')->nullable(false);
            // Поле для хранения геометрии (POINT или другие геометрические объекты)
            $table->geometry('location')->nullable(false);
            $table->index(['latitude', 'longitude']);
            // SPATIAL‑индекс для поля geometry
            $table->spatialIndex('location', 'idx_group_location');
            // Внешний ключ на таблицу объектов (если есть)
            $table->foreign('obj_id')
                ->references('id')
                ->on('objs')
                ->onDelete('cascade');

            $table->timestamp('created_at')->useCurrent();
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
