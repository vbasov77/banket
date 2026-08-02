<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Subjs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obj_id');
            $table->foreign('obj_id')
                ->references('id')->on('objs')
                ->onDelete('cascade');
            $table->string('name_subj')->nullable(); //
            $table->integer('minimum_cost')->nullable();
            $table->integer('per_person')->nullable();
            $table->integer('capacity_to')->nullable(); // Вместимость
            $table->integer('furshet')->nullable(); // Вместимость
            $table->json('site_type')->nullable();// Тип площадки: База отдыха, Банкетный зал
            $table->json('features')->nullable(); // Особенности - Можно свои б/а напитки, Выездная регистрация, Музыкальное оборудование
            $table->string('loud_music_until')->nullable();  // 22:00, 23:00, 00:00, 01:00 или 'morning'
            $table->text('text_subj')->nullable();
            $table->integer('published')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subjs');
    }
}
