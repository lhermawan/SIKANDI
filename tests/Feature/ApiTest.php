<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\ConfigurationItem;
use App\Models\User;
use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_api_login_returns_sanctum_token(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('SecurePassword123!'),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'token',
            'user' => ['id', 'name', 'email'],
        ]);
    }

    public function test_api_login_requires_2fa_when_enabled(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('SecurePassword123!'),
            'two_factor_secret' => 'SECRETKEY123456',
            'two_factor_confirmed_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'requires_2fa' => true,
        ]);
    }

    public function test_api_login_blocks_locked_account(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('SecurePassword123!'),
            'locked_until' => now()->addMinutes(15),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertStatus(423);
    }

    public function test_api_endpoints_require_authentication(): void
    {
        $response = $this->getJson('/api/v1/ci');
        $response->assertStatus(401);
    }

    public function test_authenticated_api_request_can_fetch_ci_list_and_relationships(): void
    {
        $user = User::where('email', 'admin.sandi@ciamis.go.id')->first();
        $ci = ConfigurationItem::first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/ci');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => ['id', 'ci_code', 'name', 'status'],
                ],
            ],
        ]);

        $relResponse = $this->actingAs($user, 'sanctum')->getJson("/api/v1/ci/{$ci->id}/relationships");
        $relResponse->assertStatus(200);
        $relResponse->assertJsonStructure([
            'success',
            'ci' => ['id', 'ci_code', 'name'],
            'outbound',
            'inbound',
        ]);
    }

    public function test_api_can_fetch_websites_status(): void
    {
        $user = User::where('email', 'admin.sandi@ciamis.go.id')->first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/websites/status');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'summary' => ['total', 'up', 'down'],
            'data',
        ]);
    }

    public function test_agent_cannot_access_user_endpoints(): void
    {
        $agent = Agent::firstOrCreate(
            ['agent_id' => 'AGT-TEST-001'],
            ['hostname' => 'test-server', 'status' => 'online']
        );

        $token = $agent->createToken('agent-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/security-incidents');
        $response->assertStatus(403);

        $ciResponse = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/ci');
        $ciResponse->assertStatus(403);
    }

    public function test_user_cannot_access_agent_endpoints(): void
    {
        $user = User::where('email', 'admin.sandi@ciamis.go.id')->first();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/agent/heartbeat', [
            'hostname' => 'fake-agent',
        ]);
        $response->assertStatus(403);
    }

    public function test_user_without_soc_role_cannot_access_security_incidents(): void
    {
        $opdUser = User::factory()->create([
            'is_active' => true,
        ]);
        $opdUser->assignRole('OPD User');

        $response = $this->actingAs($opdUser, 'sanctum')->getJson('/api/v1/security-incidents');
        $response->assertStatus(403);
    }
}
