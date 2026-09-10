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
        Schema::create('agent_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            
            // CPU
            $table->float('cpu_usage')->nullable();
            
            // Memory (in MB or GB, let's say bytes or just strings)
            // It's better to store raw values for memory, e.g., bytes, but bigint can hold them
            $table->unsignedBigInteger('memory_total')->nullable();
            $table->unsignedBigInteger('memory_used')->nullable();
            $table->float('memory_usage')->nullable();
            
            // Disk
            $table->unsignedBigInteger('disk_total')->nullable();
            $table->unsignedBigInteger('disk_used')->nullable();
            $table->float('disk_usage')->nullable();
            
            // Network (optional, basic)
            $table->unsignedBigInteger('rx_bytes')->nullable();
            $table->unsignedBigInteger('tx_bytes')->nullable();
            
            // Uptime
            $table->unsignedBigInteger('uptime_seconds')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_metrics');
    }
};
