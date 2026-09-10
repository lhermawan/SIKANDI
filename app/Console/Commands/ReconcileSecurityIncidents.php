<?php

namespace App\Console\Commands;

use App\Models\SecurityIncident;
use App\Models\SecurityEvent;
use App\Models\SecurityRule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileSecurityIncidents extends Command
{
    protected $signature = 'security:reconcile-incidents {--dry-run : Only show what would be done without modifying data}';
    protected $description = 'Merge duplicate security incidents and recalculate metrics (first_seen, last_seen, risk_score)';

    public function handle()
    {
        $this->info("Starting Security Incident Reconciliation...");
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("DRY RUN MODE ENABLED. No data will be permanently modified.");
        }

        // Group incidents that might be duplicates
        $groups = SecurityIncident::select('detection_rule', 'agent_id', 'source_ip')
            ->whereIn('workflow_status', ['reported', 'open', 'triage', 'investigation'])
            ->groupBy('detection_rule', 'agent_id', 'source_ip')
            ->havingRaw('COUNT(id) > 1')
            ->get();

        if ($groups->isEmpty()) {
            $this->info("No duplicate incidents found.");
            return;
        }

        foreach ($groups as $group) {
            $incidents = SecurityIncident::where('detection_rule', $group->detection_rule)
                ->where('agent_id', $group->agent_id)
                ->where('source_ip', $group->source_ip)
                ->whereIn('workflow_status', ['reported', 'open', 'triage', 'investigation'])
                ->orderBy('first_seen_at', 'asc')
                ->get();

            $primary = $incidents->first();
            $duplicates = $incidents->slice(1);

            $this->info("Found {$duplicates->count()} duplicate(s) for Campaign [{$group->detection_rule}] from IP [{$group->source_ip}]");

            if (!$isDryRun) {
                DB::transaction(function () use ($primary, $duplicates) {
                    $duplicateIds = $duplicates->pluck('id');

                    // 1. Re-link events to primary
                    SecurityEvent::whereIn('incident_id', $duplicateIds)
                        ->update(['incident_id' => $primary->id]);

                    // 2. Fetch purely RAW events (skip detections) for accurate calculation
                    $rawEvents = SecurityEvent::where('incident_id', $primary->id)
                        ->where('event_type', '!=', 'brute_force')
                        ->where('event_type', '!=', 'correlation');

                    $firstSeen = $rawEvents->min('timestamp') ?? $primary->first_seen_at;
                    $lastSeen = $rawEvents->max('timestamp') ?? $primary->last_seen_at;
                    
                    // 3. Risk Calculation
                    $rule = SecurityRule::where('name', $primary->detection_rule)->first();
                    $baseRisk = $rule ? $rule->risk_score : 50;
                    
                    $hasSuccess = $rawEvents->clone()->where(function($q){
                        $q->whereIn('event_type', ['login', 'authentication'])
                          ->whereIn('action', ['success', 'accepted', 'login_success']);
                    })->exists();

                    if ($hasSuccess) $baseRisk += 30;
                    $finalRisk = min(100, $baseRisk);

                    // 4. Update Primary
                    $primary->update([
                        'first_seen_at' => $firstSeen,
                        'last_seen_at' => $lastSeen,
                        'risk_score' => $finalRisk,
                        'description' => $primary->description . "\n\n[System Note: Reconciled and merged with " . $duplicates->count() . " duplicate incidents on " . now() . "]"
                    ]);

                    // 5. Delete Duplicates
                    SecurityIncident::whereIn('id', $duplicateIds)->delete();
                });
                
                $this->info("-> Merged into Incident ID {$primary->id} (First Seen: {$primary->first_seen_at}, Risk: {$primary->risk_score})");
            }
        }

        $this->info("Reconciliation complete.");
    }
}
