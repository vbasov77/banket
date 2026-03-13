<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DetailsObj extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('details_obj', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obj_id');
            $table->foreign('obj_id')
                ->references('id')->on('objs')
                ->onDelete('cascade');
            $table->json('for_events')->nullable(); // Для мероприятий
            $table->json('kitchen')->nullable(); // Кухня
            $table->json('service')->nullable();
            $table->string('alcohol')->nullable(); // Пробковый сбор: 0=запрещено, 1=разрешено, -X=цена
            $table->string('more')->nullable(); // Дополнительно: 0=запрещено, 1=разрешено, -X=цена
            $table->json('payment_methods')->nullable(); // Способы оплаты
            $table->text('text_obj')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('details_obj');
    }
}
