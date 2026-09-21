<?php

namespace App\Jobs;

use App\Services\ThreatIntelService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnrichIpReputationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $ipAddress;

    public bool $force;

    /**
     * Create a new job instance.
     */
    public function __construct(string $ipAddress, bool $force = false)
    {
        $this->ipAddress = $ipAddress;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(ThreatIntelService $threatIntelService): void
    {
        // Panggil ThreatIntelService untuk memproses dan menyimpan hasil ke cache database
        $threatIntelService->checkIp($this->ipAddress, $this->force);
    }
}
