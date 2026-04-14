<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $defaults = [
            ['post', 'پست و ارسال', 10],
            ['electricity', 'برق', 20],
            ['packaging', 'بسته‌بندی', 30],
            ['internet', 'اینترنت و ارتباطات', 40],
            ['other', 'سایر', 50],
        ];
        $now = now();
        foreach ($defaults as [$code, $name, $sort]) {
            DB::table('expense_categories')->insert([
                'code' => $code,
                'name' => $name,
                'sort_order' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $map = DB::table('expense_categories')->pluck('id', 'code')->all();
        $otherId = (int) ($map['other'] ?? 0);
        if ($otherId < 1) {
            throw new \RuntimeException('expense_categories seed failed');
        }

        Schema::table('business_expenses', function (Blueprint $table) use ($otherId) {
            $table->foreignId('expense_category_id')
                ->default($otherId)
                ->after('fee_amount')
                ->constrained('expense_categories')
                ->restrictOnDelete();
        });

        foreach ($map as $code => $id) {
            DB::table('business_expenses')->where('category', $code)->update(['expense_category_id' => $id]);
        }

        Schema::table('business_expenses', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('business_expenses', function (Blueprint $table) {
            $table->string('category', 32)->after('fee_amount');
            $table->index('category');
        });

        $rows = DB::table('business_expenses')
            ->select('business_expenses.id', 'expense_categories.code')
            ->join('expense_categories', 'expense_categories.id', '=', 'business_expenses.expense_category_id')
            ->get();
        foreach ($rows as $r) {
            DB::table('business_expenses')->where('id', $r->id)->update(['category' => $r->code]);
        }

        Schema::table('business_expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn('expense_category_id');
        });

        Schema::dropIfExists('expense_categories');
    }
};
