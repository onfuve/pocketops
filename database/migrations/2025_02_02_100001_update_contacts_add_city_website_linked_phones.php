<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address')->comment('Contact city (where they live/work)');
            $table->string('website')->nullable()->after('whatsapp');
            $table->foreignId('linked_contact_id')->nullable()->after('is_hamkar')->constrained('contacts')->nullOnDelete()->comment('Link to another contact (company/shop)');
        });

        Schema::create('contact_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('phone');
            $table->string('label')->nullable()->comment('e.g. موبایل, اداره');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        foreach (DB::table('contacts')->whereNotNull('phone')->get() as $row) {
            DB::table('contact_phones')->insert([
                'contact_id' => $row->id,
                'phone' => $row->phone,
                'label' => null,
                'sort' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['referrer_city', 'hamkar_company_link', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('address');
            $table->string('referrer_city')->nullable();
            $table->string('hamkar_company_link')->nullable();
        });

        foreach (DB::table('contacts')->get() as $contact) {
            $first = DB::table('contact_phones')->where('contact_id', $contact->id)->orderBy('sort')->first();
            if ($first) {
                DB::table('contacts')->where('id', $contact->id)->update(['phone' => $first->phone]);
            }
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['linked_contact_id']);
            $table->dropColumn(['city', 'website', 'linked_contact_id']);
        });

        Schema::dropIfExists('contact_phones');
    }
};
