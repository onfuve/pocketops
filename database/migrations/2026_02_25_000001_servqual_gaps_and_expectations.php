<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servqual_customer_expectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('dimension_id')->constrained('servqual_dimensions')->cascadeOnDelete();
            $table->unsignedTinyInteger('value'); // 1-5 expectation
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'dimension_id']);
        });

        Schema::table('servqual_dimensions', function (Blueprint $table) {
            $table->decimal('weight', 4, 2)->default(1)->after('sort');
        });

        Schema::table('servqual_micro_responses', function (Blueprint $table) {
            $table->foreignId('question_id')->nullable()->after('dimension_id')
                ->constrained('servqual_question_bank')->nullOnDelete();
        });

        Schema::table('customer_quality_index', function (Blueprint $table) {
            $table->json('dimension_gaps')->nullable()->after('dimension_scores');
            $table->decimal('overall_gap', 8, 2)->nullable()->after('overall_score');
            $table->json('ewma_per_dimension')->nullable()->after('dimension_gaps');
        });

        // Reliability + Assurance weighted higher (1.2) for repair business
        DB::table('servqual_dimensions')->whereIn('code', ['reliability', 'assurance'])->update(['weight' => 1.2]);
    }

    public function down(): void
    {
        Schema::table('customer_quality_index', function (Blueprint $table) {
            $table->dropColumn(['dimension_gaps', 'overall_gap', 'ewma_per_dimension']);
        });
        Schema::table('servqual_micro_responses', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });
        Schema::table('servqual_dimensions', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
        Schema::dropIfExists('servqual_customer_expectations');
    }
};
