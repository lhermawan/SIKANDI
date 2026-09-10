<?php

namespace App\Console\Commands;

use App\Models\Agent;
use Illuminate\Console\Command;

class CheckAgentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update offline/warning statuses for monitoring agents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        
        // Offline: > 5 minutes
        Agent::whereIn('status', ['online', 'warning'])
            ->where('last_seen_at', '<=', $now->copy()->subMinutes(5))
            ->update(['status' => 'offline']);
            
        // Warning: > 2 minutes but <= 5 minutes
        Agent::where('status', 'online')
            ->where('last_seen_at', '<=', $now->copy()->subMinutes(2))
            ->update(['status' => 'warning']);

        $this->info('Agent statuses checked.');
    }
}
