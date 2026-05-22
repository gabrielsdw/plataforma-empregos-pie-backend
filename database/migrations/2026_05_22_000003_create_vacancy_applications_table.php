<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('applied');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['vacancy_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_applications');
    }
};
