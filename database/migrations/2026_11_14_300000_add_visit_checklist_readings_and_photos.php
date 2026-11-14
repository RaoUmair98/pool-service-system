<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->json('checklist')->nullable()->after('notes');
            $table->decimal('reading_ph', 4, 2)->nullable()->after('checklist');
            $table->decimal('reading_free_chlorine', 5, 2)->nullable()->after('reading_ph');
            $table->decimal('reading_alkalinity', 6, 1)->nullable()->after('reading_free_chlorine');
        });

        Schema::create('visit_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('kind', 20)->default('other'); // before | after | other
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->index(['visit_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_photos');

        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['checklist', 'reading_ph', 'reading_free_chlorine', 'reading_alkalinity']);
        });
    }
};
