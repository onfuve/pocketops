<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_options', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // e.g. کارت بانکی، حساب جاری
            $table->string('value');  // card number or account number
            $table->string('type', 20)->default('card'); // card, account
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_options');
    }
};
