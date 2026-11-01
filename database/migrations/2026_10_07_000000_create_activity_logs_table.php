<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Kept as text too, so the log still names the person after the account is removed.
            $table->string('user_name')->nullable();
            $table->string('action', 30)->index();
            $table->nullableMorphs('subject');
            // What the record was called at that moment; survives later renames and deletes.
            $table->string('subject_label')->nullable()->index();
            $table->string('description');
            // [field => ['old' => ..., 'new' => ...]] in words a person can read.
            $table->json('changes')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
