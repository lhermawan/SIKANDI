<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        // 1. Configuration Items (CMDB)
        $cis = ConfigurationItem::where('name', 'like', "%{$q}%")
            ->orWhere('ci_code', 'like', "%{$q}%")
            ->orWhere('hostname', 'like', "%{$q}%")
            ->orWhere('ip_address', 'like', "%{$q}%")
            ->orWhere('domain', 'like', "%{$q}%")
            ->take(5)
            ->get();

        foreach ($cis as $ci) {
            $results[] = [
                'category' => 'CMDB Item',
                'title' => "[{$ci->ci_code}] {$ci->name}",
                'subtitle' => "IP: {$ci->ip_address} | Host: {$ci->hostname} | Status: {$ci->status}",
                'extra' => $ci->ciType?->name,
                'url' => route('cmdb.show', $ci),
            ];
        }

        // 2. IT Assets (ITAM)
        $assets = Asset::where('name', 'like', "%{$q}%")
            ->orWhere('asset_number', 'like', "%{$q}%")
            ->orWhere('serial_number', 'like', "%{$q}%")
            ->orWhere('brand', 'like', "%{$q}%")
            ->take(5)
            ->get();

        foreach ($assets as $asset) {
            $results[] = [
                'category' => 'IT Asset',
                'title' => "[{$asset->asset_number}] {$asset->name}",
                'subtitle' => "Merk: {$asset->brand} {$asset->model} | Kondisi: {$asset->condition}",
                'extra' => $asset->organization?->name,
                'url' => route('itam.show', $asset),
            ];
        }

        // 3. Service Desk Tickets
        $tickets = Ticket::where('ticket_number', 'like', "%{$q}%")
            ->orWhere('title', 'like', "%{$q}%")
            ->take(4)
            ->get();

        foreach ($tickets as $t) {
            $results[] = [
                'category' => 'Tiket Layanan',
                'title' => "[{$t->ticket_number}] {$t->title}",
                'subtitle' => "Status: {$t->status} | Prioritas: {$t->priority}",
                'extra' => $t->organization?->name,
                'url' => route('service-desk.tickets.show', $t),
            ];
        }

        // 4. Incidents
        $incidents = Incident::where('incident_number', 'like', "%{$q}%")
            ->orWhere('title', 'like', "%{$q}%")
            ->take(4)
            ->get();

        foreach ($incidents as $inc) {
            $results[] = [
                'category' => 'Insiden',
                'title' => "[{$inc->incident_number}] {$inc->title}",
                'subtitle' => "Status: {$inc->status} | Prioritas: {$inc->priority}",
                'extra' => $inc->source,
                'url' => route('incidents.show', $inc),
            ];
        }

        // 5. Monitored Websites
        $websites = Website::where('name', 'like', "%{$q}%")
            ->orWhere('url', 'like', "%{$q}%")
            ->take(4)
            ->get();

        foreach ($websites as $web) {
            $results[] = [
                'category' => 'Website Monitoring',
                'title' => $web->name,
                'subtitle' => "URL: {$web->url} | Status: ".strtoupper($web->current_status),
                'extra' => "SSL: {$web->ssl_status}",
                'url' => route('monitoring.websites'),
            ];
        }

        // 6. Organizations (OPD)
        $orgs = Organization::where('name', 'like', "%{$q}%")
            ->orWhere('code', 'like', "%{$q}%")
            ->take(4)
            ->get();

        foreach ($orgs as $org) {
            $results[] = [
                'category' => 'OPD / Lembaga',
                'title' => "[{$org->code}] {$org->name}",
                'subtitle' => $org->address,
                'extra' => ucfirst($org->category),
                'url' => route('admin.organizations.index'),
            ];
        }

        return response()->json($results);
    }
}
