<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_link_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('identifier', 100)->nullable(); // for multi mode e.g. mobile
            $table->timestamp('first_accessed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('data')->nullable(); // per-module answers and file refs
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
