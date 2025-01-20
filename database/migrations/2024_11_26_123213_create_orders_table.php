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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('order_status_id');
            $table->integer('total_quantity');
            $table->tinyInteger('payment_method')->comment('1->COD');
            $table->double('sub_total');
            $table->double('total');
            $table->foreign('address_id')->references('id')->on('address');
            $table->foreign('order_status_id')->references('id')->on('order_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
