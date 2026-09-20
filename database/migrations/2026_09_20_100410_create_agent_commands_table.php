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
        Schema::create('agent_commands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('action');
            $table->json('paths');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->longText('result')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commands');
    }
};
