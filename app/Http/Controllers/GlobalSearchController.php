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

        $user = auth()->user();
        $isGlobalAdmin = $user && $user->hasAnyRole(['Super Admin', 'Admin Persandian', 'IT Technician']);
        $userOrgId = $user?->organization_id;

        // 1. Configuration Items (CMDB)
        $ciQuery = ConfigurationItem::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('ci_code', 'like', "%{$q}%")
                ->orWhere('hostname', 'like', "%{$q}%")
                ->orWhere('ip_address', 'like', "%{$q}%")
                ->orWhere('domain', 'like', "%{$q}%");
        });
        if (! $isGlobalAdmin && $userOrgId) {
            $ciQuery->where('organization_id', $userOrgId);
        }
        $cis = $ciQuery->take(5)->get();

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
        $assetQuery = Asset::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('asset_number', 'like', "%{$q}%")
                ->orWhere('serial_number', 'like', "%{$q}%")
                ->orWhere('brand', 'like', "%{$q}%");
        });
        if (! $isGlobalAdmin && $userOrgId) {
            $assetQuery->where('organization_id', $userOrgId);
        }
        $assets = $assetQuery->take(5)->get();

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
        $ticketQuery = Ticket::where(function ($query) use ($q) {
            $query->where('ticket_number', 'like', "%{$q}%")
                ->orWhere('title', 'like', "%{$q}%");
        });
        if (! $isGlobalAdmin && $userOrgId) {
            $ticketQuery->where('organization_id', $userOrgId);
        }
        $tickets = $ticketQuery->take(4)->get();

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
        $incQuery = Incident::where(function ($query) use ($q) {
            $query->where('incident_number', 'like', "%{$q}%")
                ->orWhere('title', 'like', "%{$q}%");
        });
        if (! $isGlobalAdmin && $userOrgId) {
            $incQuery->where('organization_id', $userOrgId);
        }
        $incidents = $incQuery->take(4)->get();

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
        $webQuery = Website::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('url', 'like', "%{$q}%");
        });
        if (! $isGlobalAdmin && $userOrgId) {
            $webQuery->where('organization_id', $userOrgId);
        }
        $websites = $webQuery->take(4)->get();

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
        $orgQuery = Organization::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('code', 'like', "%{$q}%");
        });
        if (! $isGlobalAdmin && $userOrgId) {
            $orgQuery->where('id', $userOrgId);
        }
        $orgs = $orgQuery->take(4)->get();

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
