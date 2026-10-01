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
        Schema::create('obj_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obj_id');
            $table->foreign('obj_id')
                ->references('id')->on('objs')
                ->onDelete('cascade');

            // Банкет
            $table->text('banquet_note')->nullable();         // Примечание
            $table->string('prepayment')->nullable();          // Предоплата

            // Текстиль
            $table->json('textile_package')->nullable();       // Текстильный пакет
            $table->text('textile_colors')->nullable();        // Расцветки текстильного пакета

            // Доп. информация
            $table->json('tables')->nullable();              // Столы
            $table->string('parking')->nullable();              // Парковка
            $table->string('pier')->nullable();                  // Причал

            // Оборудование — все 12 полей в одном JSON
            // own_equipment, projector_screen, music_stage,
            // sound_equipment, light_equipment, dimming_system,
            // karaoke, wifi, dance_floor, air_conditioner,
            // wardrobe, dressing_rooms
            $table->json('equipment')->nullable();

            // Детское — 3 поля в одном JSON
            // kids_room, kids_menu, kids_corner
            $table->json('kids')->nullable();

            // Описание
            $table->text('interior')->nullable();              // Интерьер
            $table->json('location')->nullable();              // Месторасположение
            $table->timestamp('created_at')->useCurrent();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
