<?php

namespace App\Http\Controllers;

use App\Exports\WebsitesExport;
use App\Imports\WebsitesImport;
use App\Jobs\CheckWebsitesBatch;
use App\Models\ConfigurationItem;
use App\Models\Organization;
use App\Models\Website;
use App\Services\WebsiteMonitoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    public function websites(Request $request): View
    {
        $query = Website::with(['configurationItem.ciType', 'organization'])
            ->latest('last_checked_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
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
            'url' => [
                'required',
                'url',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (! WebsiteMonitoringService::isSafePublicUrl($value)) {
                        $fail('URL website tidak valid atau mengarah ke IP privat / loopback internal.');
                    }
                },
            ],
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
            'url' => [
                'required',
                'url',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (! WebsiteMonitoringService::isSafePublicUrl($value)) {
                        $fail('URL website tidak valid atau mengarah ke IP privat / loopback internal.');
                    }
                },
            ],
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
                $ci ? $ci->id : '1',
            ],
        ];

        $export = new class($template) implements FromArray
        {
            protected $template;

            public function __construct($template)
            {
                $this->template = $template;
            }

            public function array(): array
            {
                return $this->template;
            }
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
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        Excel::import(new WebsitesImport, $request->file('file'));

        return back()->with('success', 'Berhasil mengimpor website ke dalam monitoring.');
    }

    public function check(Website $website): RedirectResponse
    {
        $service = app(WebsiteMonitoringService::class);
        $service->check($website);

        return back()->with('success', "Pemeriksaan untuk {$website->name} selesai. Status: ".strtoupper($website->fresh()->current_status));
    }

    public function checkAll(Request $request)
    {
        $websiteIds = Website::pluck('id')->toArray();

        $jobs = [];
        foreach ($websiteIds as $websiteId) {
            $jobs[] = new CheckWebsitesBatch($websiteId);
        }

        $batch = Bus::batch($jobs)
            ->name('Bulk Website Monitoring')
            ->dispatch();

        return response()->json(['batch_id' => $batch->id]);
    }

    public function batchStatus($id)
    {
        $batch = Bus::findBatch($id);

        if (! $batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        return response()->json([
            'id' => $batch->id,
            'progress' => $batch->progress(),
            'finished' => $batch->processedJobs(),
            'totalJobs' => $batch->totalJobs,
            'is_finished' => $batch->finished(),
        ]);
    }
}
