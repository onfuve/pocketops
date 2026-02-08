<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_options', function (Blueprint $table) {
            $table->string('card_number', 50)->nullable()->after('value')->comment('شماره کارت');
            $table->string('iban', 34)->nullable()->after('card_number')->comment('شماره شبا');
            $table->string('account_number', 50)->nullable()->after('iban')->comment('شماره حساب');
            $table->boolean('print_card_number')->default(true)->after('account_number');
            $table->boolean('print_iban')->default(true)->after('print_card_number');
            $table->boolean('print_account_number')->default(true)->after('print_iban');
        });
    }

    public function down(): void
    {
        Schema::table('payment_options', function (Blueprint $table) {
            $table->dropColumn([
                'card_number', 'iban', 'account_number',
                'print_card_number', 'print_iban', 'print_account_number',
            ]);
        });
    }
};
