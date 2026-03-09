<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImgSubj extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('img_subj', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subj_id');
            $table->foreign('subj_id')
                ->references('id')->on('subjs')
                ->onDelete('cascade');
            $table->bigInteger('photo_id');
            $table->string('path');
            $table->string('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('img_subj');
    }
}
