<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performers', function (Blueprint $table) {
            $table->unsignedInteger('min_package_price')->nullable()->after('price_from');
            $table->index('min_package_price');
        });
    }

    public function down(): void
    {
        Schema::table('performers', function (Blueprint $table) {
            $table->dropIndex(['min_package_price']);
            $table->dropColumn('min_package_price');
        });
    }


};
