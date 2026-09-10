<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true);
            $table->integer('threshold')->default(5);
            $table->integer('time_window_seconds')->default(300);
            $table->string('severity')->default('high');
            $table->integer('risk_score')->default(25);
            $table->boolean('auto_incident')->default(true);
            $table->timestamps();
        });

        // Add columns to existing security_incidents
        Schema::table('security_incidents', function (Blueprint $table) {
            $table->integer('risk_score')->default(0);
            $table->string('source_ip')->nullable()->index();
            $table->string('username')->nullable()->index();
            $table->string('detection_rule')->nullable();
            $table->dateTime('first_seen_at')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            
            // Adjust enums if necessary, but we'll use workflow_status for status mapping.
            // Let's add a general string status for EDR to not clash with strict CSIRT enums if needed
            $table->string('edr_status')->default('OPEN')->index();
        });

        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->cascadeOnDelete();
            $table->dateTime('timestamp')->index();
            $table->string('event_type')->index();
            $table->string('action')->index();
            $table->string('hostname')->nullable();
            $table->string('username')->nullable()->index();
            $table->string('source_ip')->nullable()->index();
            $table->string('process')->nullable();
            $table->string('severity')->default('info')->index();
            $table->integer('risk_score')->default(0);
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('incident_id')->nullable()->constrained('security_incidents')->nullOnDelete();
            $table->timestamps();
        });
        
        // Add default rules
        DB::table('security_rules')->insert([
            ['name' => 'BRUTE_FORCE', 'enabled' => true, 'threshold' => 10, 'time_window_seconds' => 300, 'severity' => 'high', 'risk_score' => 50, 'auto_incident' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PASSWORD_SPRAYING', 'enabled' => true, 'threshold' => 5, 'time_window_seconds' => 300, 'severity' => 'critical', 'risk_score' => 88, 'auto_incident' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'USERNAME_ENUMERATION', 'enabled' => true, 'threshold' => 10, 'time_window_seconds' => 300, 'severity' => 'medium', 'risk_score' => 40, 'auto_incident' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ACCOUNT_COMPROMISE', 'enabled' => true, 'threshold' => 1, 'time_window_seconds' => 300, 'severity' => 'critical', 'risk_score' => 90, 'auto_incident' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
        
        Schema::table('security_incidents', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['risk_score', 'source_ip', 'username', 'detection_rule', 'first_seen_at', 'last_seen_at', 'agent_id', 'edr_status']);
        });

        Schema::dropIfExists('security_rules');
    }
};
