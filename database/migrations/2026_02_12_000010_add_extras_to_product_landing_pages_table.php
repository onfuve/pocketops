<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->string('price_format', 30)->nullable()->default('rial')->after('show_price');
            $table->boolean('show_notes')->default(false)->after('primary_color');
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
            $table->boolean('show_share_buttons')->default(false)->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'price_format', 'show_notes', 'notes_text',
                'show_social', 'social_instagram', 'social_telegram', 'social_whatsapp',
                'show_address', 'address_text',
                'show_contact', 'contact_phone', 'contact_email',
                'show_share_buttons',
            ]);
        });
    }
};
