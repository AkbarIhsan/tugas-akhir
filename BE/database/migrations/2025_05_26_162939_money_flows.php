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
        Schema::create('money_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_flow_type')->constrained('flow_type');
            $table->foreignId('id_user')->constrained('users');
            $table->integer('qty_money');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_flows');
    }
};
