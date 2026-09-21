<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Website;
use App\Services\WebsiteMonitoringService;
use Illuminate\Support\Facades\Log;

class CheckWebsitesBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $websiteIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $websiteIds)
    {
        $this->websiteIds = $websiteIds;
    }

    /**
     * Execute the job.
     */
    public function handle(WebsiteMonitoringService $monitoringService): void
    {
        $websites = Website::whereIn('id', $this->websiteIds)->get();

        foreach ($websites as $website) {
            try {
                $monitoringService->check($website);
            } catch (\Exception $e) {
                Log::error("Failed to monitor website ID {$website->id}: " . $e->getMessage());
            }
        }
    }
}
