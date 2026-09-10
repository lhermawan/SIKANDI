<?php

namespace Tests\Feature;

use App\Models\CiType;
use App\Models\ConfigurationItem;
use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;

class CmdbTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('email', 'admin.sandi@ciamis.go.id')->first();
    }

    public function test_cmdb_index_is_accessible_by_authenticated_user(): void
    {
        $response = $this->actingAs($this->admin)->get('/cmdb');
        $response->assertStatus(200);
        $response->assertSee('Configuration Items (CI)');
    }

    public function test_cmdb_graph_view_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get('/cmdb/graph');
        $response->assertStatus(200);
        $response->assertSeeText('Topologi & Relasi Visual CMDB');
    }

    public function test_cmdb_graph_data_endpoint_returns_json_nodes_and_edges(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/cmdb/graph/data');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'nodes' => [
                '*' => ['id', 'label', 'title', 'shape', 'color', 'ci_code'],
            ],
            'edges' => [
                '*' => ['from', 'to', 'label'],
            ],
        ]);
    }

    public function test_cmdb_ci_show_page_displays_relationships(): void
    {
        $ci = ConfigurationItem::first();
        $this->assertNotNull($ci);

        $response = $this->actingAs($this->admin)->get("/cmdb/{$ci->id}");
        $response->assertStatus(200);
        $response->assertSee($ci->ci_code);
        $response->assertSee($ci->name);
    }

    public function test_creating_new_ci_generates_standard_sequential_code(): void
    {
        $serverType = CiType::where('code', 'server')->first();
        $org = Organization::first();

        $ci = ConfigurationItem::create([
            'ci_type_id' => $serverType->id,
            'organization_id' => $org->id,
            'name' => 'SRV-TEST-VALIDATION-01',
            'status' => 'active',
            'criticality' => 'high',
            'ip_address' => '10.10.99.1',
        ]);

        $this->assertMatchesRegularExpression('/^CI-[A-Z0-9]{3}-\d{5}$/', $ci->ci_code);
    }
}
