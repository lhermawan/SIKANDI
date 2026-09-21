<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Risk;
use App\Models\SecurityIncident;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('risk:evaluate')]
#[Description('Evaluate dynamic risk scores based on active security incidents')]
class EvaluateDynamicRisk extends Command
{
    public function handle()
    {
        $this->info('Starting dynamic risk evaluation...');

        $risks = Risk::whereNotNull('ci_id')->where('status', 'open')->get();
        $updatedCount = 0;

        foreach ($risks as $risk) {
            $agentIds = Agent::where('ci_id', $risk->ci_id)->pluck('id');

            if ($agentIds->isEmpty()) {
                continue;
            }

            $activeIncidents = SecurityIncident::whereIn('agent_id', $agentIds)
                ->whereIn('status', ['pending', 'in_progress', 'open', 'investigating'])
                ->count();

            if ($activeIncidents > 0) {
                if ($risk->likelihood < 5) {
                    $risk->likelihood = 5;
                    $risk->is_dynamic_score = true;
                    $risk->save(); // will trigger calculateRiskScore

                    $this->info("Risk {$risk->risk_code} likelihood bumped to 5 (Almost Certain) due to active security incidents on its CI.");
                    $updatedCount++;
                }
            }
        }

        $this->info("Evaluation complete. Updated $updatedCount risks.");
    }
}
