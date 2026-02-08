<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_options', function (Blueprint $table) {
            $table->string('holder_name', 100)->nullable()->after('label')->comment('نام صاحب حساب/کارت');
            $table->string('bank_name', 100)->nullable()->after('holder_name')->comment('نام بانک');
        });
    }

    public function down(): void
    {
        Schema::table('payment_options', function (Blueprint $table) {
            $table->dropColumn(['holder_name', 'bank_name']);
        });
    }
};
