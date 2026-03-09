<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImgObj extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('img_obj', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obj_id');
            $table->unsignedBigInteger('photo_id');
            $table->foreign('obj_id')
                ->references('id')->on('objs')
                ->onDelete('cascade');
            $table->string('path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('img_obj');
    }
}
