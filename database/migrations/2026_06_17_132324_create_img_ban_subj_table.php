<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImgBanSubjTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('img_ban_subj', function (Blueprint $table) {
            $table->id(); // Автоинкрементный первичный ключ
            $table->unsignedBigInteger('subj_id'); // ID сущности (например, товара, поста и т. д.)

            $table->foreign('subj_id')
                ->references('id')->on('subjs')
                ->onDelete('cascade');

            $table->text('big_id')->nullable(); // Полный URL изображения (может быть пустым)
            $table->text('big_img')->nullable(); // Полный URL изображения (может быть пустым)
            $table->text('small_id')->nullable(); // Полный URL изображения (может быть пустым)
            $table->text('small_img')->nullable(); // Полный URL изображения (может быть пустым)
            $table->integer('position');

            // Индексы для ускорения поиска и соединений
            $table->index('subj_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('img_ban_subj');
    }
}
