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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ci_id')->nullable()->constrained('configuration_items')->nullOnDelete();
            $table->string('agent_id')->unique();
            $table->string('hostname')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('os')->nullable();
            $table->string('os_version')->nullable();
            $table->string('agent_version')->nullable();
            $table->string('status')->default('pending'); // pending, online, warning, offline, disabled, revoked
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
