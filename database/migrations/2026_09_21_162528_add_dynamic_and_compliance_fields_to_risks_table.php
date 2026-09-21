<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('risks', function (Blueprint $table) {
            $table->string('compliance_framework')->nullable()->after('impact');
            $table->string('compliance_clause')->nullable()->after('compliance_framework');
            $table->decimal('financial_impact_estimate', 20, 2)->nullable()->after('compliance_clause');
            $table->integer('downtime_hours_estimate')->nullable()->after('financial_impact_estimate');
            $table->boolean('is_dynamic_score')->default(false)->after('downtime_hours_estimate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risks', function (Blueprint $table) {
            $table->dropColumn([
                'compliance_framework',
                'compliance_clause',
                'financial_impact_estimate',
                'downtime_hours_estimate',
                'is_dynamic_score',
            ]);
        });
    }
};
