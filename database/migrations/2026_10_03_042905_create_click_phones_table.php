<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('click_phone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subj_id');
            $table->string('ip', 45); // IPv6 занимает до 45 символов
            $table->timestamp('created_at')->useCurrent();

            // покрывает и проверку дублей, и выборку статистики по subj
            $table->index(['subj_id', 'ip', 'created_at']);
            $table->index('ip');

            $table->foreign('subj_id')
                ->references('id')
                ->on('subjs')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('click_phone');
    }
};
