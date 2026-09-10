<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom SOC di security_incidents
        Schema::table('security_incidents', function (Blueprint $table) {
            $table->string('target_type')->nullable()->after('source_ip');
            $table->string('target_value')->nullable()->after('target_type');
            $table->dateTime('detected_at')->nullable()->after('last_seen_at');
            $table->dateTime('contained_at')->nullable()->after('detected_at');
            $table->dateTime('resolved_at')->nullable()->after('contained_at');
            $table->string('resolution_type')->nullable();
            $table->text('resolution_summary')->nullable();
            $table->string('root_cause')->nullable();
        });

        // 2. security_incident_tasks
        Schema::create('security_incident_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('security_incidents')->cascadeOnDelete();
            $table->string('category');
            $table->string('task');
            $table->text('description')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, COMPLETED
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. security_incident_evidence
        Schema::create('security_incident_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('security_incidents')->cascadeOnDelete();
            $table->string('type'); // LOG, SCREENSHOT, FILE, COMMAND_OUTPUT, NETWORK_CAPTURE, DOCUMENT, OTHER
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_hash')->nullable(); // SHA256
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('collected_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 4. security_incident_responses
        Schema::create('security_incident_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('security_incidents')->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('status')->default('COMPLETED'); // PENDING, IN_PROGRESS, COMPLETED, FAILED, NOT_APPLICABLE
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('performed_at')->nullable();
            $table->text('result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. security_incident_assignments
        Schema::create('security_incident_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('security_incidents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('unassigned_at')->nullable();
            $table->timestamps();
        });

        // 6. security_incident_audit_logs
        Schema::create('security_incident_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('security_incidents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_incident_audit_logs');
        Schema::dropIfExists('security_incident_assignments');
        Schema::dropIfExists('security_incident_responses');
        Schema::dropIfExists('security_incident_evidence');
        Schema::dropIfExists('security_incident_tasks');

        Schema::table('security_incidents', function (Blueprint $table) {
            $table->dropColumn([
                'target_type', 'target_value', 'detected_at', 'contained_at', 
                'resolved_at', 'resolution_type', 'resolution_summary', 'root_cause'
            ]);
        });
    }
};