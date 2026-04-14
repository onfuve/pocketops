<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_expenses', function (Blueprint $table) {
            $table->decimal('fee_amount', 18, 0)->nullable()->after('amount')->comment('Card/transfer fee in rials');
        });

        Schema::table('business_expenses', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
        });

        Schema::table('business_expenses', function (Blueprint $table) {
            $table->dropColumn('bank_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('business_expenses', function (Blueprint $table) {
            $table->dropColumn('fee_amount');
        });

        Schema::table('business_expenses', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('category')->constrained()->nullOnDelete();
        });
    }
};
