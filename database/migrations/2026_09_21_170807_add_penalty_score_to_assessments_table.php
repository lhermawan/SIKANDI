<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->decimal('penalty_score', 5, 2)->default(0)->after('compliance_score');
            $table->decimal('final_score', 5, 2)->default(0)->after('penalty_score');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['penalty_score', 'final_score']);
        });
    }
};
