<?php

namespace App\Console\Commands;

use App\Models\AgentMetric;
use App\Models\AgentEvent;
use Illuminate\Console\Command;

class PruneAgentMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:prune-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old agent metrics and events to save database space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffMetrics = now()->subDays(30);
        $deletedMetrics = AgentMetric::where('created_at', '<', $cutoffMetrics)->delete();

        $cutoffEvents = now()->subDays(90);
        $deletedEvents = AgentEvent::where('created_at', '<', $cutoffEvents)->delete();

        $this->info("Pruned {$deletedMetrics} old agent metrics and {$deletedEvents} old agent events.");
    }
}
