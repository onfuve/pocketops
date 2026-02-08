<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('code_global', 100)->nullable()->after('description')->comment('Barcode / global identifier');
            $table->string('code_internal', 100)->nullable()->after('code_global')->comment('Internal SKU / code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['code_global', 'code_internal']);
        });
    }
};
