<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('lead_channel_id')->nullable()->after('email')->constrained('lead_channels')->nullOnDelete();
            $table->foreignId('referrer_contact_id')->nullable()->after('lead_channel_id')->constrained('contacts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['lead_channel_id']);
            $table->dropForeign(['referrer_contact_id']);
        });
    }
};
