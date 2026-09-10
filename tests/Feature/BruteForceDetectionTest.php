<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\SecurityEvent;
use App\Models\SecurityIncident;
use App\Models\SecurityRule;
use App\Services\SecurityDetectionEngine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BruteForceDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected $agent;
    protected $rule;
    protected $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = Agent::factory()->create(['hostname' => 'test-server']);
        
        // Seed exact rule from production constraints
        $this->rule = SecurityRule::create([
            'name' => 'BRUTE_FORCE',
            'enabled' => true,
            'threshold' => 5,
            'time_window_seconds' => 300,
            'severity' => 'high',
            'risk_score' => 50,
            'auto_incident' => true,
        ]);

        $this->engine = new SecurityDetectionEngine();
    }

    private function simulateRawLoginFailed($ip, $username, $timestamp)
    {
        $this->engine->processEvent($this->agent, [
            'event_type' => 'login',
            'action' => 'failed',
            'source_ip' => $ip,
            'username' => $username,
            'timestamp' => $timestamp->timestamp,
            'risk_score' => 0
        ]);
    }
    
    private function simulateDetectionEvent($ip, $username, $timestamp)
    {
        $this->engine->processEvent($this->agent, [
            'event_type' => 'brute_force',
            'action' => 'detected',
            'source_ip' => $ip,
            'username' => $username,
            'timestamp' => $timestamp->timestamp,
            'risk_score' => 0
        ]);
    }

    /** @test */
    public function test_1_five_raw_failed_logins_create_one_incident()
    {
        $now = Carbon::now();
        
        for ($i = 0; $i < 5; $i++) {
            $this->simulateRawLoginFailed('10.0.0.1', 'root', $now->copy()->addSeconds($i * 10));
        }

        $incidents = SecurityIncident::all();
        $this->assertCount(1, $incidents);
        $this->assertEquals('BRUTE_FORCE', $incidents->first()->detection_rule);
        $this->assertEquals(50, $incidents->first()->risk_score);
    }

    /** @test */
    public function test_2_ten_raw_failed_logins_create_only_one_campaign()
    {
        $now = Carbon::now();
        
        for ($i = 0; $i < 10; $i++) {
            $this->simulateRawLoginFailed('10.0.0.2', 'admin', $now->copy()->addSeconds($i * 10));
        }

        $incidents = SecurityIncident::all();
        $this->assertCount(1, $incidents, "10 failed logins should merge into 1 incident campaign.");
    }

    /** @test */
    public function test_3_detection_events_are_not_counted_as_raw_events()
    {
        $now = Carbon::now();
        
        // 10 raw events
        for ($i = 0; $i < 10; $i++) {
            $this->simulateRawLoginFailed('10.0.0.3', 'user1', $now->copy()->addSeconds($i * 10));
        }
        
        // 5 detection events
        for ($i = 0; $i < 5; $i++) {
            $this->simulateDetectionEvent('10.0.0.3', 'user1', $now->copy()->addSeconds(100 + ($i * 10)));
        }

        $incident = SecurityIncident::first();
        
        // Ensure description contains correct counts (10 raw, 5 detection, 15 total)
        $this->assertStringContainsString('Raw Login Attempts : 10', $incident->description);
        $this->assertStringContainsString('Detection Events   : 5', $incident->description);
    }

    /** @test */
    public function test_4_risk_score_is_not_inflated_by_count()
    {
        $now = Carbon::now();
        
        // Even 20 raw events shouldn't push risk score beyond base if no escalation evidence
        for ($i = 0; $i < 20; $i++) {
            $this->simulateRawLoginFailed('10.0.0.4', 'root', $now->copy()->addSeconds($i * 5));
        }

        $incident = SecurityIncident::first();
        $this->assertEquals(50, $incident->risk_score);
    }

    /** @test */
    public function test_7_first_seen_and_last_seen_calculation()
    {
        $start = Carbon::create(2026, 9, 10, 19, 12, 48);
        $end = Carbon::create(2026, 9, 10, 19, 19, 16);
        
        $this->simulateRawLoginFailed('10.0.0.7', 'root', $start);
        $this->simulateRawLoginFailed('10.0.0.7', 'root', $start->copy()->addMinute());
        $this->simulateRawLoginFailed('10.0.0.7', 'root', $start->copy()->addMinutes(2));
        $this->simulateRawLoginFailed('10.0.0.7', 'root', $start->copy()->addMinutes(3));
        $this->simulateRawLoginFailed('10.0.0.7', 'root', $end);

        $incident = SecurityIncident::first();
        
        $this->assertEquals($start->toDateTimeString(), $incident->first_seen_at->toDateTimeString());
        $this->assertEquals($end->toDateTimeString(), $incident->last_seen_at->toDateTimeString());
    }

    /** @test */
    public function test_9_changing_rule_affects_new_events_without_code_changes()
    {
        $now = Carbon::now();
        
        for ($i = 0; $i < 5; $i++) {
            $this->simulateRawLoginFailed('10.0.0.9', 'test', $now->copy()->addSeconds($i * 5));
        }

        $this->assertEquals(50, SecurityIncident::first()->risk_score);

        // Administrator changes rule via UI
        $this->rule->update(['risk_score' => 70, 'severity' => 'critical']);

        // Subsequent event in the campaign
        $this->simulateRawLoginFailed('10.0.0.9', 'test', $now->copy()->addSeconds(30));

        $incident = SecurityIncident::first();
        $this->assertEquals(70, $incident->risk_score);
        $this->assertEquals('critical', $incident->severity);
    }
}
