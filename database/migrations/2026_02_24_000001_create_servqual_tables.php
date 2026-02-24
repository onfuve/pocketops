<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servqual_dimensions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('name_fa')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('servqual_question_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained('servqual_dimensions')->cascadeOnDelete();
            $table->string('text', 200);
            $table->string('text_fa', 200)->nullable();
            $table->unsignedTinyInteger('weight')->default(1);
            $table->boolean('is_reverse_scored')->default(false);
            $table->string('service_type', 64)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('servqual_micro_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('form_submission_id')->nullable()->constrained('form_submissions')->nullOnDelete();
            $table->foreignId('dimension_id')->constrained('servqual_dimensions')->cascadeOnDelete();
            $table->unsignedTinyInteger('value'); // 1-5 Likert
            $table->string('form_link_code', 24)->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'dimension_id']);
            $table->index(['invoice_id', 'created_at']);
        });

        Schema::create('customer_quality_index', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->json('dimension_scores')->nullable();
            $table->decimal('recency_weighted_score', 5, 2)->nullable();
            $table->decimal('confidence_ratio', 5, 4)->nullable(); // dimensions_with_data / 5
            $table->json('risk_flags')->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique('contact_id');
            $table->index('overall_score');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->boolean('is_servqual_micro')->default(false)->after('submission_mode');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('is_servqual_micro');
        });
        Schema::dropIfExists('customer_quality_index');
        Schema::dropIfExists('servqual_micro_responses');
        Schema::dropIfExists('servqual_question_bank');
        Schema::dropIfExists('servqual_dimensions');
    }
};
