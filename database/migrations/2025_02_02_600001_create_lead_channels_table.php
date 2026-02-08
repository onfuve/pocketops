<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('e.g. تلفن, معرف, وب‌سایت, تبلیغات');
            $table->boolean('is_referral')->default(false)->comment('When true, lead must link to a referrer contact');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        DB::table('lead_channels')->insert([
            ['name' => 'تلفن', 'is_referral' => false, 'sort' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'معرف', 'is_referral' => true, 'sort' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'وب‌سایت', 'is_referral' => false, 'sort' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'تبلیغات', 'is_referral' => false, 'sort' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'سایر', 'is_referral' => false, 'sort' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_channels');
    }
};
