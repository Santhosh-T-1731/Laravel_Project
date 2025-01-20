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
        Schema::create('address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name',100);
            $table->string('phone_number',50);
            $table->text('address_1');
            $table->text('address_2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city',100);
            $table->string('state',30);
            $table->string('zip_code',30);
            $table->enum('address_type',['home','office'])->comment('1->home,2->office');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
