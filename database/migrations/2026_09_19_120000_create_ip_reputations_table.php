<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_reputations', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->boolean('is_public')->default(true);
            $table->integer('abuse_confidence_score')->default(0);
            $table->string('country_code')->nullable();
            $table->string('usage_type')->nullable();
            $table->string('isp')->nullable();
            $table->string('domain')->nullable();
            $table->integer('total_reports')->default(0);
            $table->json('raw_data')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_reputations');
    }
};
