<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 18, 0);
            $table->date('paid_at');
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete()->comment('Payment made to/through this contact');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('paid_at');
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
