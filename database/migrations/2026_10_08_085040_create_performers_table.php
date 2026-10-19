<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('catalog_categories')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('phone', 32)->nullable();
            $table->json('video_url')->nullable();
            $table->integer('published')->default(0); // публикуем после модерации
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performers');
    }
};
