<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique()->index();
            $table->string('source', 50)->default('whatsapp');
            $table->string('whatsapp_from', 80)->nullable()->index();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_contact')->nullable();
            $table->string('incident_type')->nullable()->index();
            $table->dateTime('incident_time')->nullable();
            $table->string('affected_asset')->nullable();
            $table->text('chronology')->nullable();
            $table->text('impact')->nullable();
            $table->text('evidence_note')->nullable();
            $table->json('attachments')->nullable();

            // Triage Review Status: pending_review, verified, rejected
            $table->string('status', 30)->default('pending_review')->index();
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();

            // Relasi ke insiden keamanan resmi jika laporan diverifikasi
            $table->foreignId('security_incident_id')->nullable()->constrained('security_incidents')->nullOnDelete();

            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_incident_reports');
    }
};
