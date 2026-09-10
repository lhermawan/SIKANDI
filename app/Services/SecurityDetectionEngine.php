<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\SecurityEvent;
use App\Models\SecurityIncident;
use App\Models\SecurityRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SecurityDetectionEngine
{
    public function processEvent(Agent $agent, array $payload)
    {
        // 1. Store the raw event
        $event = SecurityEvent::create([
            'event_id' => $payload['event_id'] ?? (string)\Illuminate\Support\Str::uuid(),
            'agent_id' => $agent->id,
            'timestamp' => isset($payload['timestamp']) && is_numeric($payload['timestamp']) ? Carbon::createFromTimestamp($payload['timestamp']) : now(),
            'event_type' => $payload['event_type'] ?? 'unknown',
            'action' => $payload['action'] ?? 'unknown',
            'hostname' => $payload['hostname'] ?? $agent->hostname,
            'username' => $payload['username'] ?? null,
            'source_ip' => $payload['source_ip'] ?? null,
            'process' => $payload['process'] ?? null,
            'severity' => strtolower($payload['severity'] ?? 'info'),
            'risk_score' => (int)($payload['risk_score'] ?? 0),
            'reason' => $payload['reason'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
        ]);

        // 2. Run correlations and rules
        $this->evaluateRules($event);
    }

    protected function evaluateRules(SecurityEvent $event)
    {
        $rules = SecurityRule::where('enabled', true)->get();
        
        foreach ($rules as $rule) {
            if ($rule->name === 'BRUTE_FORCE') {
                $this->detectBruteForce($event, $rule);
            } elseif ($rule->name === 'PASSWORD_SPRAYING') {
                $this->detectPasswordSpraying($event, $rule);
            } elseif ($rule->name === 'ACCOUNT_COMPROMISE') {
                $this->detectAccountCompromise($event, $rule);
            }
        }
    }

    protected function detectBruteForce(SecurityEvent $event, SecurityRule $rule)
    {
        if ($event->event_type !== 'login' && $event->event_type !== 'authentication') return;
        if ($event->action !== 'failed' && $event->action !== 'login_failed') return;
        if (empty($event->source_ip) || empty($event->username)) return;

        $since = Carbon::parse($event->timestamp)->subSeconds($rule->time_window_seconds);

        $failedCount = SecurityEvent::where('event_type', $event->event_type)
            ->whereIn('action', ['failed', 'login_failed'])
            ->where('agent_id', $event->agent_id)
            ->where('source_ip', $event->source_ip)
            ->where('username', $event->username)
            ->where('timestamp', '>=', $since)
            ->count();

        if ($failedCount >= $rule->threshold) {
            $this->createOrUpdateIncident(
                $event, 
                $rule, 
                "BRUTE_FORCE", 
                "Multiple failed authentication attempts detected for user {$event->username} from {$event->source_ip}.",
                "unauthorized_access",
                ['source_ip' => $event->source_ip, 'username' => $event->username]
            );
        }
    }

    protected function detectPasswordSpraying(SecurityEvent $event, SecurityRule $rule)
    {
        if ($event->event_type !== 'login' && $event->event_type !== 'authentication') return;
        if ($event->action !== 'failed' && $event->action !== 'login_failed') return;
        if (empty($event->source_ip)) return;

        $since = Carbon::parse($event->timestamp)->subSeconds($rule->time_window_seconds);

        $uniqueUsers = SecurityEvent::where('event_type', $event->event_type)
            ->whereIn('action', ['failed', 'login_failed'])
            ->where('agent_id', $event->agent_id)
            ->where('source_ip', $event->source_ip)
            ->where('timestamp', '>=', $since)
            ->distinct('username')
            ->count('username');

        if ($uniqueUsers >= $rule->threshold) {
            $this->createOrUpdateIncident(
                $event, 
                $rule, 
                "PASSWORD_SPRAYING", 
                "Possible Password Spraying from IP {$event->source_ip} targeting {$uniqueUsers} unique usernames.",
                "unauthorized_access",
                ['source_ip' => $event->source_ip]
            );
        }
    }

    protected function detectAccountCompromise(SecurityEvent $event, SecurityRule $rule)
    {
        if ($event->event_type !== 'login' && $event->event_type !== 'authentication') return;
        if ($event->action !== 'success' && $event->action !== 'login_success') return;
        if (empty($event->source_ip) || empty($event->username)) return;

        $since = Carbon::parse($event->timestamp)->subSeconds($rule->time_window_seconds);

        // Check if there are failures preceding this success
        $failedCount = SecurityEvent::where('event_type', $event->event_type)
            ->whereIn('action', ['failed', 'login_failed'])
            ->where('agent_id', $event->agent_id)
            ->where('source_ip', $event->source_ip)
            ->where('username', $event->username)
            ->where('timestamp', '>=', $since)
            ->where('timestamp', '<', $event->timestamp)
            ->count();

        if ($failedCount >= $rule->threshold) { // e.g. >= 1
            $this->createOrUpdateIncident(
                $event, 
                $rule, 
                "ACCOUNT_COMPROMISE", 
                "Successful login by {$event->username} from {$event->source_ip} after multiple failed attempts.",
                "account_compromise",
                ['source_ip' => $event->source_ip, 'username' => $event->username]
            );
        }
    }

    protected function createOrUpdateIncident(SecurityEvent $triggerEvent, SecurityRule $rule, $ruleName, $description, $incidentType, $fingerprint = [])
    {
        if (!$rule->auto_incident) return;

        // Find existing open incident with same fingerprint
        $query = SecurityIncident::where('detection_rule', $ruleName)
            ->where('agent_id', $triggerEvent->agent_id)
            ->whereIn('edr_status', ['OPEN', 'INVESTIGATING']);
            
        if (isset($fingerprint['source_ip'])) {
            $query->where('source_ip', $fingerprint['source_ip']);
        }
        if (isset($fingerprint['username'])) {
            $query->where('username', $fingerprint['username']);
        }
        
        $incident = $query->first();

        if ($incident) {
            // Update existing
            $incident->last_seen_at = $triggerEvent->timestamp;
            if ($triggerEvent->risk_score > $incident->risk_score) {
                $incident->risk_score = $triggerEvent->risk_score; // Update to highest seen
            }
            // Add rule risk score bonus
            if ($incident->risk_score < 100) {
                $incident->risk_score = min(100, $incident->risk_score + ($rule->risk_score / 2));
            }
            $incident->save();
            
            // Link event
            $triggerEvent->update(['incident_id' => $incident->id]);
            
            // Also link past related events within window
            $since = Carbon::parse($triggerEvent->timestamp)->subSeconds($rule->time_window_seconds);
            $q = SecurityEvent::where('agent_id', $triggerEvent->agent_id)
                ->where('timestamp', '>=', $since)
                ->whereNull('incident_id');
            if (isset($fingerprint['source_ip'])) $q->where('source_ip', $fingerprint['source_ip']);
            if (isset($fingerprint['username'])) $q->where('username', $fingerprint['username']);
            $q->update(['incident_id' => $incident->id]);

        } else {
            // Organization logic: Fallback to agent's organization if available, or 1
            $orgId = 1;
            
            $newIncident = SecurityIncident::create([
                'title' => str_replace('_', ' ', $ruleName),
                'incident_type' => $incidentType,
                'severity' => $rule->severity,
                'workflow_status' => 'reported',
                'edr_status' => 'OPEN',
                'organization_id' => $orgId,
                'ci_id' => $triggerEvent->agent->ci_id ?? null,
                'reporter_id' => 1, // System/Admin user by default for auto-generated
                'description' => $description,
                'risk_score' => min(100, $triggerEvent->risk_score + $rule->risk_score),
                'source_ip' => $fingerprint['source_ip'] ?? $triggerEvent->source_ip,
                'username' => $fingerprint['username'] ?? $triggerEvent->username,
                'detection_rule' => $ruleName,
                'first_seen_at' => $triggerEvent->timestamp,
                'last_seen_at' => $triggerEvent->timestamp,
                'agent_id' => $triggerEvent->agent_id,
            ]);

            $triggerEvent->update(['incident_id' => $newIncident->id]);
            
            // Link past related events within window
            $since = Carbon::parse($triggerEvent->timestamp)->subSeconds($rule->time_window_seconds);
            $q = SecurityEvent::where('agent_id', $triggerEvent->agent_id)
                ->where('timestamp', '>=', $since)
                ->whereNull('incident_id');
            if (isset($fingerprint['source_ip'])) $q->where('source_ip', $fingerprint['source_ip']);
            if (isset($fingerprint['username'])) $q->where('username', $fingerprint['username']);
            $q->update(['incident_id' => $newIncident->id]);
        }
    }
}
