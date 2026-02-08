<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('instagram')->nullable();
            $table->string('telegram')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('referrer_name')->nullable()->comment('Person or company who referred');
            $table->string('referrer_city')->nullable();
            $table->boolean('is_hamkar')->default(false)->comment('Partner/collaborator');
            $table->string('hamkar_company_link')->nullable()->comment('Link to company/shop/business');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
