<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\WebsiteMonitoringService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckWebsitesBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 2;

    public int $backoff = 30;

    protected $websiteId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $websiteId)
    {
        $this->websiteId = $websiteId;
    }

    /**
     * Execute the job.
     */
    public function handle(WebsiteMonitoringService $monitoringService): void
    {
        $website = Website::find($this->websiteId);
        if (! $website) {
            return;
        }

        try {
            $monitoringService->check($website);
        } catch (\Exception $e) {
            Log::error("Failed to monitor website ID {$website->id}: ".$e->getMessage());
            throw $e; // Throw exception agar Laravel Worker me-retry job ini (atau masuk ke failed_jobs)
        }
    }
}
