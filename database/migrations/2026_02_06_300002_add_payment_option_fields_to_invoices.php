<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('payment_option_fields')->nullable()->after('payment_option_ids')
                ->comment('Per-option print overrides: { "option_id": { "print_card_number": true, "print_iban": true, "print_account_number": false } }');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_option_fields');
        });
    }
};
