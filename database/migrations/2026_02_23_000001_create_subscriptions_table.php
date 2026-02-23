<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');
            $table->string('category', 30); // cloud, vpn, license, domain, other
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('expiry_date');
            $table->string('billing_cycle', 20); // monthly, quarterly, yearly, custom
            $table->decimal('price', 15, 0)->default(0);
            $table->decimal('cost', 15, 0)->nullable();
            $table->string('payment_status', 20)->default('pending'); // paid, pending, overdue
            $table->boolean('auto_renewal')->default(false);
            $table->string('supplier')->nullable();
            $table->text('account_credentials')->nullable(); // encrypted in model
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('reminder_days_before')->nullable(); // 3, 7, 14 for X days before expiry
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['expiry_date', 'payment_status']);
            $table->index(['contact_id']);
            $table->index(['category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
