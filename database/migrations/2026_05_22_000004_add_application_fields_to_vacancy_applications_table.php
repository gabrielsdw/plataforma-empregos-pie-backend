<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancy_applications', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('candidate_id');
            $table->string('portfolio_url')->nullable()->after('phone');
            $table->text('cover_letter')->nullable()->after('portfolio_url');
        });
    }

    public function down(): void
    {
        Schema::table('vacancy_applications', function (Blueprint $table) {
            $table->dropColumn(['phone', 'portfolio_url', 'cover_letter']);
        });
    }
};
