<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();   // fotografi, videografy, dj, vedushchie, oformlenie
            $table->string('name');             // Фотографы, Видеографы, DJ, Ведущие, Оформление залов
            $table->integer('position')->default(0);
            $table->integer('published')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_categories');
    }
};
