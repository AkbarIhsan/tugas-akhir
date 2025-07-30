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
        Schema::table('transfer_stock', function (Blueprint $table) {
            $table->dropColumn(['product_price_gives', 'total_price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_stock', function (Blueprint $table) {
            $table->integer('product_price_gives')->nullable();
            $table->integer('total_price')->nullable();
        });
    }
};
