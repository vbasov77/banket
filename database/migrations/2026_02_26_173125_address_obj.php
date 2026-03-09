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
    {Schema::create('address_objs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('obj_id');
        $table->foreign('obj_id')
            ->references('id')->on('objs')
            ->onDelete('cascade');
        $table->json('address');
        $table->decimal('latitude', 10, 8);
        $table->decimal('longitude', 11, 8);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_objs');
    }
};
