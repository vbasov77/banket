<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ips', function (Blueprint $table) {
            $table->id();                           // id (bigint, auto increment)
            $table->string('ip', 45);               // хватит и для IPv4, и для IPv6
            $table->timestamp('created_at')        // created_at
            ->useCurrent();                   // DEFAULT CURRENT_TIMESTAMP

            // Индексы должны быть ВНУТРИ этого блока (до закрывающей скобки)
            $table->index('created_at');
            $table->index('ip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ips');
    }
};
