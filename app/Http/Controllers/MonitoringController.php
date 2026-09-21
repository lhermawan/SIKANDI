<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\Organization;
use App\Models\Website;
use App\Models\WebsiteCheckLog;
use App\Exports\WebsitesExport;
use App\Imports\WebsitesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function websites(Request $request): View
    {
        $query = Website::with(['configurationItem.ciType', 'organization'])
            ->latest('last_checked_at');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'ssl_warning') {
                $query->whereIn('ssl_status', ['expiring_soon', 'expired', 'invalid']);
            } else {
                $query->where('current_status', $status);
            }
        }

        if ($opd = $request->input('organization_id')) {
            $query->where('organization_id', $opd);
        }

        $websites = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Website::count(),
            'up' => Website::where('current_status', 'up')->count(),
            'down' => Website::where('current_status', 'down')->count(),
            'ssl_warning' => Website::whereIn('ssl_status', ['expiring_soon', 'expired', 'invalid'])->count(),
            'avg_response_time' => round(Website::where('current_status', 'up')->avg('response_time_ms') ?? 0),
        ];

        $organizations = Organization::orderBy('name')->get();
        $configurationItems = ConfigurationItem::orderBy('name')->get();

        return view('monitoring.websites', compact('websites', 'stats', 'organizations', 'configurationItems'));
    }

    public function storeWebsite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'ci_id' => 'required|exists:configuration_items,id',
        ]);

        $website = Website::create($validated);

        return back()->with('success', "Website {$website->name} berhasil ditambahkan ke monitoring.");
    }

    public function update(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'ci_id' => 'required|exists:configuration_items,id',
        ]);

        $website->update($validated);

        return back()->with('success', "Website {$website->name} berhasil diperbarui.");
    }

    public function destroy(Website $website): RedirectResponse
    {
        $name = $website->name;
        $website->delete();

        return back()->with('success', "Website {$name} berhasil dihapus dari monitoring.");
    }

    public function downloadTemplate()
    {
        $org = Organization::first();
        $ci = ConfigurationItem::first();
        
        $template = [
            ['name', 'url', 'organization_id', 'ci_id'],
            [
                'Website Resmi Dummy',
                'https://example.ciamiskab.go.id',
                $org ? $org->id : '1',
                $ci ? $ci->id : '1'
            ]
        ];
        
        $export = new class($template) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $template;
            public function __construct($template) { $this->template = $template; }
            public function array(): array { return $this->template; }
        };

        return Excel::download($export, 'template_import_website.xlsx');
    }

    public function export(Request $request)
    {
        return Excel::download(new WebsitesExport($request), 'laporan_website_monitoring.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        Excel::import(new WebsitesImport, $request->file('file'));

        return back()->with('success', "Berhasil mengimpor website ke dalam monitoring.");
    }

    public function check(Website $website): RedirectResponse
    {
        $service = app(\App\Services\WebsiteMonitoringService::class);
        $service->check($website);

        return back()->with('success', "Pemeriksaan untuk {$website->name} selesai. Status: ".strtoupper($website->fresh()->current_status));
    }
    
    public function checkAll(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Artisan::call('sikandi:check-websites');
        return back()->with('success', "Proses pengecekan seluruh website sedang berjalan di background.");
    }
}
