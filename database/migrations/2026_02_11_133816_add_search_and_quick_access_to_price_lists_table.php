<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->boolean('show_search')->default(true)->after('show_photos');
            $table->boolean('show_quick_access')->default(true)->after('show_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropColumn(['show_search', 'show_quick_access']);
        });
    }
};
