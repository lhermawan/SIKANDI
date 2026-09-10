<?php

namespace Tests\Feature;

use App\Models\ConfigurationItem;
use App\Models\User;
use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_api_login_returns_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'superadmin@ciamis.go.id',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'token',
            'user' => ['id', 'name', 'email'],
        ]);
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
}
