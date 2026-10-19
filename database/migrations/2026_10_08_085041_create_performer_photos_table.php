<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performer_photos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('performer_id'); // ID исполнителя
            $table->foreign('performer_id')
                ->references('id')->on('performers')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('big_id')->nullable();
            $table->text('big_img')->nullable();
            $table->text('small_id')->nullable();
            $table->text('small_img')->nullable();
            $table->integer('position')->default(0);

            $table->index('performer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performer_photos');
    }
};
