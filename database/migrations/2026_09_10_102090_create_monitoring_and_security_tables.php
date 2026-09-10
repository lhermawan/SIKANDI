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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ci_id')->constrained('configuration_items')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('url')->index();
            $table->integer('check_interval_minutes')->default(5);
            $table->enum('current_status', ['up', 'down', 'warning', 'unknown'])->default('unknown')->index();
            $table->integer('http_status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->string('ip_address')->nullable();

            $table->enum('ssl_status', ['valid', 'expiring_soon', 'expired', 'invalid', 'no_ssl', 'unknown'])->default('unknown')->index();
            $table->string('ssl_issuer')->nullable();
            $table->dateTime('ssl_expires_at')->nullable();

            $table->dateTime('last_checked_at')->nullable();
            $table->dateTime('last_status_change_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('website_check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('websites')->cascadeOnDelete();
            $table->enum('status', ['up', 'down', 'warning']);
            $table->integer('http_status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('ssl_valid')->default(false);
            $table->integer('ssl_days_left')->nullable();
            $table->dateTime('checked_at')->useCurrent()->index();
        });

        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_code')->unique()->index(); // e.g. SEC-2026-0001
            $table->string('title');
            $table->enum('incident_type', [
                'malware', 'phishing', 'defacement', 'unauthorized_access',
                'account_compromise', 'data_exposure', 'vulnerability',
                'website_attack', 'network_attack', 'other',
            ])->index();

            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium')->index();
            $table->enum('workflow_status', [
                'reported', 'triage', 'investigation', 'containment', 'eradication', 'recovery', 'closed',
            ])->default('reported')->index();

            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('ci_id')->nullable()->constrained('configuration_items')->nullOnDelete();
            $table->foreignId('reporter_id')->constrained('users');
            $table->foreignId('assigned_lead_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('description');
            $table->string('attack_vector')->nullable();
            $table->text('impact_summary')->nullable();
            $table->text('containment_actions')->nullable();
            $table->text('recovery_actions')->nullable();
            $table->string('evidence_file_path')->nullable();

            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
        Schema::dropIfExists('website_check_logs');
        Schema::dropIfExists('websites');
    }
};
