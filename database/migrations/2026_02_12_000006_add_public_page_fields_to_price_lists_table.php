<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->boolean('show_cta')->default(false)->after('price_format');
            $table->string('cta_url')->nullable()->after('show_cta');
            $table->string('cta_text')->nullable()->after('cta_url');
            $table->boolean('show_notes')->default(false)->after('cta_text');
            $table->text('notes_text')->nullable()->after('show_notes');
            $table->boolean('show_social')->default(false)->after('notes_text');
            $table->string('social_instagram')->nullable()->after('show_social');
            $table->string('social_telegram')->nullable()->after('social_instagram');
            $table->string('social_whatsapp')->nullable()->after('social_telegram');
            $table->boolean('show_address')->default(false)->after('social_whatsapp');
            $table->text('address_text')->nullable()->after('show_address');
            $table->boolean('show_contact')->default(false)->after('address_text');
            $table->string('contact_phone')->nullable()->after('show_contact');
            $table->string('contact_email')->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropColumn([
                'show_cta', 'cta_url', 'cta_text',
                'show_notes', 'notes_text',
                'show_social', 'social_instagram', 'social_telegram', 'social_whatsapp',
                'show_address', 'address_text',
                'show_contact', 'contact_phone', 'contact_email',
            ]);
        });
    }
};
