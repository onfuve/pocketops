<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50)->unique()->nullable();
            $table->string('headline')->nullable();
            $table->string('subheadline')->nullable();
            $table->string('cta_type', 30)->default('link'); // purchase, call, whatsapp, link
            $table->string('cta_url')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('template', 30)->default('hero'); // hero, minimal, card, split
            $table->string('primary_color', 20)->nullable();
            $table->boolean('show_price')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_landing_pages');
    }
};
