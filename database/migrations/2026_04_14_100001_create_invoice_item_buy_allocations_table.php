<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_item_buy_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sell_invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
            $table->foreignId('buy_invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->timestamps();

            $table->unique(['sell_invoice_item_id', 'buy_invoice_item_id'], 'invoice_item_buy_alloc_unique_pair');
            $table->index('buy_invoice_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_item_buy_allocations');
    }
};
