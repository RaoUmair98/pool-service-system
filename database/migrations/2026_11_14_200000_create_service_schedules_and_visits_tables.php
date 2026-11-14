<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pool_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('technician_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 0=Sunday … 6=Saturday (Carbon)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('scheduled_date');
            $table->string('status', 20)->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('skip_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['pool_id', 'scheduled_date']);
            $table->index(['company_id', 'scheduled_date', 'status']);
            $table->index(['technician_user_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
        Schema::dropIfExists('service_schedules');
    }
};
