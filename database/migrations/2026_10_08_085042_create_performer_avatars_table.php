<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performer_avatars', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('performer_id');
            $table->foreign('performer_id')
                ->references('id')->on('performers')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('small_id')->nullable();
            $table->text('small_img')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performer_avatars');
    }
};
