<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->string('type', 20)->default('reminder'); // reminder, lead_task, invoice_due
            $table->string('remindable_type')->nullable(); // Lead, Invoice
            $table->unsignedBigInteger('remindable_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('done_at')->nullable();
            $table->timestamps();

            $table->index(['due_date', 'done_at']);
            $table->index(['remindable_type', 'remindable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
