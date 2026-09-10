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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('IT Service');
            $table->integer('sla_response_hours')->default(2);
            $table->integer('sla_resolution_hours')->default(24);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique()->index(); // e.g. TKT-2026-0001
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('ci_id')->nullable()->constrained('configuration_items')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('category', [
                'service_request', 'incident', 'question', 'access_request', 'maintenance',
            ])->default('service_request')->index();

            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium')->index();
            $table->enum('status', [
                'open', 'assigned', 'in_progress', 'waiting', 'resolved', 'closed',
            ])->default('open')->index();

            $table->string('title');
            $table->text('description');
            $table->string('attachment_path')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->dateTime('sla_due_at')->nullable();
            $table->dateTime('first_response_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->boolean('is_internal')->default(false);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number')->unique()->index(); // e.g. INC-2026-0001
            $table->string('title');
            $table->enum('source', [
                'service_desk', 'monitoring', 'security_monitoring', 'manual_report',
            ])->default('manual_report')->index();

            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('ci_id')->nullable()->constrained('configuration_items')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium')->index();
            $table->enum('status', [
                'open', 'investigation', 'in_progress', 'resolved', 'closed',
            ])->default('open')->index();

            $table->text('impact_description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('resolution')->nullable();

            $table->dateTime('detected_at');
            $table->dateTime('resolved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('incident_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_comments');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('ticket_comments');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('services');
    }
};
