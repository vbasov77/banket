<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performer_packages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performer_id')
                ->constrained('performers')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_min')->nullable();
            $table->unsignedInteger('price_max')->nullable();
            $table->boolean('is_addon')->default(false);
            $table->integer('sort')->default(0);

            $table->timestamps();

            $table->index(['performer_id', 'sort']);
            $table->index('price_min');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performer_packages');
    }
};
