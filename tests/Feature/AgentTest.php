<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\ConfigurationItem;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AgentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set the global registration token
        SystemSetting::create([
            'key' => 'agent_registration_token',
            'value' => 'test-secret-token'
        ]);
    }

    public function test_agent_can_register_with_valid_token()
    {
        $response = $this->postJson('/api/v1/agent/register', [
            'registration_token' => 'test-secret-token',
            'hostname' => 'web-server-01',
            'os' => 'Ubuntu',
            'os_version' => '22.04',
            'agent_version' => '1.0.0'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'agent_id', 'status']);
        
        $this->assertDatabaseHas('agents', [
            'hostname' => 'web-server-01',
            'status' => 'pending'
        ]);
    }

    public function test_agent_registration_fails_with_invalid_token()
    {
        $response = $this->postJson('/api/v1/agent/register', [
            'registration_token' => 'wrong-token',
            'hostname' => 'web-server-01'
        ]);

        $response->assertStatus(401);
    }

    public function test_approved_agent_can_send_heartbeat()
    {
        $agent = Agent::factory()->create(['status' => 'online']);
        $token = $agent->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/agent/heartbeat');

        $response->assertStatus(200);
        
        // Assert last seen updated
        $this->assertTrue($agent->fresh()->last_seen_at->isToday());
    }

    public function test_agent_can_send_metrics()
    {
        $agent = Agent::factory()->create(['status' => 'online']);
        $token = $agent->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/agent/metrics', [
            'cpu_usage' => 45.5,
            'memory_usage' => 60.0
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('agent_metrics', [
            'agent_id' => $agent->id,
            'cpu_usage' => 45.5
        ]);
    }

    public function test_agent_can_trigger_incident_when_service_down()
    {
        // Require a CI linked
        $ciType = \App\Models\CiType::create(['name' => 'Server', 'code' => 'SRV']);
        $org = \App\Models\Organization::create(['name' => 'Test Org', 'code' => 'ORG1']);
        $ci = ConfigurationItem::create(['name' => 'Web Server CI', 'ci_type_id' => $ciType->id, 'organization_id' => $org->id]);
        $agent = Agent::factory()->create(['status' => 'online', 'ci_id' => $ci->id]);
        $token = $agent->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/agent/events', [
            'type' => 'service_down',
            'severity' => 'high',
            'message' => 'MySQL stopped',
            'payload' => ['service' => 'mysql']
        ]);

        $response->assertStatus(200);
        
        // Verify incident created
        $this->assertDatabaseHas('incidents', [
            'ci_id' => $ci->id,
            'status' => 'open'
        ]);
        
        // Verify event created and linked
        $this->assertDatabaseHas('agent_events', [
            'agent_id' => $agent->id,
            'type' => 'service_down'
        ]);
        
        $event = \App\Models\AgentEvent::first();
        $this->assertNotNull($event->incident_id);
    }
}
