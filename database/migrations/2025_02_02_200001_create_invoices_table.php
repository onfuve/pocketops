<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10); // 'sell', 'buy'
            $table->string('invoice_number')->nullable();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('draft'); // draft, final, paid, cancelled
            $table->decimal('subtotal', 15, 0)->default(0);
            $table->decimal('discount', 15, 0)->default(0);
            $table->decimal('total', 15, 0)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 15, 0)->default(0);
            $table->decimal('amount', 15, 0)->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
