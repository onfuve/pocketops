<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('todo'); // todo, in_progress, done, cancelled
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->string('taskable_type'); // Lead, Invoice, Contact
            $table->unsignedBigInteger('taskable_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['taskable_type', 'taskable_id']);
            $table->index(['status', 'due_date']);
        });

        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('tasks');
    }
};
