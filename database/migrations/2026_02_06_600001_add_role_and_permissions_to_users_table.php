<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('team')->after('email');
            $table->boolean('can_delete_invoice')->default(false)->after('role');
            $table->boolean('can_delete_contact')->default(false)->after('can_delete_invoice');
            $table->boolean('can_delete_lead')->default(false)->after('can_delete_contact');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'can_delete_invoice', 'can_delete_contact', 'can_delete_lead']);
        });
    }
};
