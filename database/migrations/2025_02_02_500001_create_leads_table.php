<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Counterpart / contact name');
            $table->string('company')->nullable()->comment('Company or organization');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('source')->nullable()->comment('Where the lead came from: website, referral, etc.');
            $table->text('details')->nullable()->comment('Notes, description, requirements');
            $table->string('status')->default('new')->comment('Pipeline stage: new, contacted, qualified, proposal, negotiation, won, lost');
            $table->decimal('value', 15, 0)->nullable()->comment('Estimated deal value (Rial)');
            $table->date('lead_date')->nullable()->comment('Date of lead / first contact');
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete()->comment('Linked contact when converted');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
