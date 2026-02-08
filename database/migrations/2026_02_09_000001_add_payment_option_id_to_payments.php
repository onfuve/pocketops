<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_transactions', function (Blueprint $table) {
            $table->foreignId('payment_option_id')->nullable()->after('bank_account_id')->constrained('payment_options')->nullOnDelete();
        });
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreignId('payment_option_id')->nullable()->after('bank_account_id')->constrained('payment_options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contact_transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_option_id']);
        });
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropForeign(['payment_option_id']);
        });
    }
};
