<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class ServiceDeskTest extends TestCase
{
    public function test_opd_user_can_view_ticket_list(): void
    {
        $opdUser = User::where('email', 'opd.dinkes@ciamis.go.id')->first();

        $response = $this->actingAs($opdUser)->get('/service-desk/tickets');
        $response->assertStatus(200);
    }

    public function test_user_can_create_ticket_with_sequential_code(): void
    {
        $opdUser = User::where('email', 'opd.dinkes@ciamis.go.id')->first();
        $service = Service::first();

        $response = $this->actingAs($opdUser)->post('/service-desk/tickets', [
            'service_id' => $service->id,
            'category' => 'service_request',
            'title' => 'Permohonan Pemasangan Sertifikat Elektronik Baru',
            'description' => 'Mohon bantuan penerbitan sertifikat elektronik TTE untuk pejabat baru di Dinas Kesehatan.',
            'priority' => 'medium',
        ]);

        $ticket = Ticket::where('title', 'Permohonan Pemasangan Sertifikat Elektronik Baru')->latest('id')->first();
        $this->assertNotNull($ticket);
        $this->assertMatchesRegularExpression('/^TKT-\d{4}-\d{4}$/', $ticket->ticket_number);

        $response->assertRedirect("/service-desk/tickets/{$ticket->id}");
    }

    public function test_opd_user_cannot_view_ticket_belonging_to_another_opd(): void
    {
        $opdUser = User::where('email', 'opd.dinkes@ciamis.go.id')->first();
        $otherOrg = Organization::where('id', '!=', $opdUser->organization_id)->first();
        $service = Service::first();

        // Create ticket for another org
        $otherTicket = Ticket::create([
            'organization_id' => $otherOrg->id,
            'service_id' => $service->id,
            'requester_id' => 1,
            'title' => 'Tiket Rahasia OPD Lain',
            'description' => 'Data sensitif internal OPD lain',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $response = $this->actingAs($opdUser)->get("/service-desk/tickets/{$otherTicket->id}");
        $response->assertStatus(403);
    }
}
