<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users_vk', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vk_id')->unique(); // VK ID пользователя
            $table->unsignedBigInteger('user_id'); // Внутренний ID пользователя сайта
            $table->string('first_name')->nullable(); // Имя
            $table->string('last_name')->nullable(); // Фамилия
            $table->string('bdate')->nullable(); // Дата рождения
            $table->text('photo')->nullable(); //Фото
            $table->string('email')->nullable(); // Email
            $table->text('access_token'); // Токен доступа (может быть длинным)
            $table->text('refresh_token')->nullable(); // Refresh-токен
            $table->timestamp('token_expires_at')->nullable(); // Срок действия токена
            $table->timestamps();

            // Внешний ключ на таблицу пользователей сайта
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });


    }

    public function down()
    {
        Schema::dropIfExists('users_vk');
    }
};
