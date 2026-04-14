<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_item_expense_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sell_invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
            $table->foreignId('business_expense_id')->constrained('business_expenses')->cascadeOnDelete();
            $table->unsignedBigInteger('amount_rial');
            $table->timestamps();

            $table->unique(['sell_invoice_item_id', 'business_expense_id'], 'invoice_item_expense_alloc_unique_pair');
            $table->index('business_expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_item_expense_allocations');
    }
};
