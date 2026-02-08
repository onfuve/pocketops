<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete()->comment('Contact we receive from or pay to');
            $table->string('type', 10)->comment('receive or pay');
            $table->decimal('amount', 18, 0);
            $table->date('paid_at');
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('counterparty_contact_id')->nullable()->constrained('contacts')->nullOnDelete()->comment('Payment via this contact');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('paid_at');
            $table->index(['contact_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_transactions');
    }
};
