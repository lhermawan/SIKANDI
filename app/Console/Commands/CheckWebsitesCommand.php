<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Jobs\CheckWebsitesBatch;

class CheckWebsitesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sikandi:check-websites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all websites in the monitoring list by dispatching chunked jobs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting website health checks...');

        // Ambil semua ID website yang perlu dicek
        $websiteIds = Website::pluck('id')->toArray();

        if (empty($websiteIds)) {
            $this->info('No websites found to check.');
            return;
        }

        // Pecah menjadi beberapa bagian (chunk) agar tidak memberatkan memori
        $chunks = array_chunk($websiteIds, 50);

        foreach ($chunks as $chunk) {
            CheckWebsitesBatch::dispatch($chunk);
        }

        $this->info(count($websiteIds) . ' websites dispatched for checking in ' . count($chunks) . ' batches.');
    }
}
