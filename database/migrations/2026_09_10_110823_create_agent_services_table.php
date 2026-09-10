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
        Schema::create('agent_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('service_name');
            $table->string('status'); // RUNNING, STOPPED, FAILED, UNKNOWN
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            
            // Ensures we only have one record per service per agent, we can just update it
            $table->unique(['agent_id', 'service_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_services');
    }
};
