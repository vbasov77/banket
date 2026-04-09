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
    {
        Schema::create('address_subjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subj_id');
            $table->unsignedBigInteger('group_id');
            $table->foreign('subj_id')
                ->references('id')->on('subjs')
                ->onDelete('cascade');
            $table->integer('city_id');
            $table->integer('district_id');
            $table->json('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_subjs');
    }
};
