<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50); // created, status_changed, note_updated, attachment_added, user_assigned, user_unassigned
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};
