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
        // Source of truth: Security Rules from database
        $rules = SecurityRule::where('enabled', true)->get();
        
        foreach ($rules as $rule) {
            if ($rule->name === 'BRUTE_FORCE') {
                $this->detectBruteForce($event, $rule);
            } elseif ($rule->name === 'PASSWORD_SPRAYING') {
                $this->detectPasswordSpraying($event, $rule);
            } elseif ($rule->name === 'USERNAME_ENUMERATION') {
                $this->detectUsernameEnumeration($event, $rule);
            } elseif ($rule->name === 'ACCOUNT_COMPROMISE') {
                $this->detectAccountCompromise($event, $rule);
            }
        }
    }

    protected function classifyEvent(SecurityEvent $event): array
    {
        $type   = strtolower($event->event_type ?? '');
        $action = strtolower($event->action ?? '');

        $combined = $type . '_' . $action;

        $isLoginFail = in_array($type, ['login', 'authentication', 'auth', 'loginfailed', 'login_failed', 'failed_login'])
            || in_array($action, ['failed', 'login_failed', 'failure', 'denied'])
            || str_contains($type, 'loginfail')
            || str_contains($type, 'login_fail')
            || str_contains($type, 'failedlogin')
            || str_contains($combined, 'fail');

        $isLoginSuccess = (
            in_array($type, ['login', 'authentication', 'auth', 'loginsuccess', 'login_success'])
            && in_array($action, ['success', 'login_success', 'accepted', 'ok'])
        ) || in_array($type, ['loginsuccess', 'login_success', 'success_login']);

        if (str_contains($type, 'fail')) {
            $isLoginSuccess = false;
        }

        return [
            'is_login_fail'    => $isLoginFail,
            'is_login_success' => $isLoginSuccess,
        ];
    }

    protected function queryLoginFailed($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereIn('event_type', ['login', 'authentication', 'auth'])
                    ->whereIn('action', ['failed', 'login_failed', 'failure', 'denied']);
            })->orWhereIn('event_type', [
                'loginfailed', 'login_failed', 'failed_login',
                'LOGINFAILED', 'LOGIN_FAILED', 'FAILED_LOGIN',
            ])->orWhere(function ($inner) {
                $inner->where('event_type', 'like', '%login%')
                    ->where('event_type', 'like', '%fail%');
            });
        });
    }

    protected function detectBruteForce(SecurityEvent $event, SecurityRule $rule)
    {
        $cls = $this->classifyEvent($event);
        if (! $cls['is_login_fail']) return;
        if (empty($event->source_ip) || empty($event->username)) return;

        $since = Carbon::parse($event->timestamp)->subSeconds($rule->time_window_seconds);

        $failedCount = $this->queryLoginFailed(
            SecurityEvent::where('agent_id', $event->agent_id)
                ->where('source_ip', $event->source_ip)
                ->where('username', $event->username)
                ->where('timestamp', '>=', $since)
        )->count();

        if ($failedCount >= $rule->threshold) {
            $this->createOrUpdateIncident(
                triggerEvent: $event,
                rule: $rule,
                ruleName: $rule->name,
                description: "Multiple failed authentication attempts ({$failedCount} times) detected for user {$event->username} from {$event->source_ip}.",
                incidentType: 'unauthorized_access',
                fingerprint: ['source_ip' => $event->source_ip, 'username' => $event->username]
            );
        }
    }

    protected function detectUsernameEnumeration(SecurityEvent $event, SecurityRule $rule)
    {
        $cls = $this->classifyEvent($event);
        if (! $cls['is_login_fail']) return;
        if (empty($event->source_ip)) return;

        $since = Carbon::parse($event->timestamp)->subSeconds($rule->time_window_seconds);

        // Fetch all failed logins from this IP within window
        $events = $this->queryLoginFailed(
            SecurityEvent::where('agent_id', $event->agent_id)
                ->where('source_ip', $event->source_ip)
                ->where('timestamp', '>=', $since)
        )->get();

        $uniqueUsernames = $events->pluck('username')->filter()->unique();
        $uniqueCount = $uniqueUsernames->count();

        if ($uniqueCount >= $rule->threshold) {
            // Check if there is an existing PASSWORD_SPRAYING rule that this might fall under
            $sprayingRule = SecurityRule::where('name', 'PASSWORD_SPRAYING')->where('enabled', true)->first();
            $escalateToSpraying = $sprayingRule && $uniqueCount >= $sprayingRule->threshold;

            $activeRule = $escalateToSpraying ? $sprayingRule : $rule;
            $ruleName = $activeRule->name;

            $usernamesList = $uniqueUsernames->implode("\n- ");
            $desc = "Detected login attempts against {$uniqueCount} unique usernames from IP {$event->source_ip}.\n\nTarget Usernames:\n- " . $usernamesList;

            $this->createOrUpdateIncident(
                triggerEvent: $event,
                rule: $activeRule,
                ruleName: $ruleName,
                description: $desc,
                incidentType: 'unauthorized_access',
                fingerprint: ['source_ip' => $event->source_ip]
            );
        }
    }

    protected function detectPasswordSpraying(SecurityEvent $event, SecurityRule $rule)
    {
        // Delegate detection to Username Enumeration to avoid duplicate processing.
        // It handles escalation internally.
        $this->detectUsernameEnumeration($event, $rule);
    }

    protected function detectAccountCompromise(SecurityEvent $event, SecurityRule $rule)
    {
        $cls = $this->classifyEvent($event);
        if (! $cls['is_login_success']) return;
        if (empty($event->source_ip) || empty($event->username)) return;

        $since = Carbon::parse($event->timestamp)->subSeconds($rule->time_window_seconds);

        $failedCount = $this->queryLoginFailed(
            SecurityEvent::where('agent_id', $event->agent_id)
                ->where('source_ip', $event->source_ip)
                ->where('username', $event->username)
                ->where('timestamp', '>=', $since)
                ->where('timestamp', '<', $event->timestamp)
        )->count();

        if ($failedCount >= $rule->threshold) {
            $this->createOrUpdateIncident(
                triggerEvent: $event,
                rule: $rule,
                ruleName: $rule->name,
                description: "Successful login by {$event->username} from {$event->source_ip} after {$failedCount} failed attempts within {$rule->time_window_seconds} seconds.",
                incidentType: 'account_compromise',
                fingerprint: ['source_ip' => $event->source_ip, 'username' => $event->username]
            );
        }
    }

    protected function createOrUpdateIncident(SecurityEvent $triggerEvent, SecurityRule $rule, $ruleName, $description, $incidentType, $fingerprint = [])
    {
        // If not auto-incident, we only generate the raw event/log (which is already done in processEvent)
        if (!$rule->auto_incident) return;

        // Base query for exact deduplication in current time window
        $since = Carbon::parse($triggerEvent->timestamp)->subSeconds($rule->time_window_seconds);
        
        $query = SecurityIncident::whereIn('detection_rule', [$ruleName, 'USERNAME_ENUMERATION', 'PASSWORD_SPRAYING']) // Group related attack vectors
            ->where('agent_id', $triggerEvent->agent_id)
            ->where('last_seen_at', '>=', $since)
            ->whereNotIn('workflow_status', ['closed', 'resolved']); // Don't merge into explicitly closed incidents
            
        if (isset($fingerprint['source_ip'])) {
            $query->where('source_ip', $fingerprint['source_ip']);
        }
        if (isset($fingerprint['username']) && $ruleName !== 'PASSWORD_SPRAYING' && $ruleName !== 'USERNAME_ENUMERATION') {
            $query->where('username', $fingerprint['username']);
        }
        
        $incident = $query->latest('last_seen_at')->first();

        // Accumulate related events query
        $relatedEventsQuery = SecurityEvent::where('agent_id', $triggerEvent->agent_id)
            ->where('timestamp', '>=', $since);
        if (isset($fingerprint['source_ip'])) $relatedEventsQuery->where('source_ip', $fingerprint['source_ip']);
        if (isset($fingerprint['username']) && $ruleName !== 'PASSWORD_SPRAYING' && $ruleName !== 'USERNAME_ENUMERATION') {
            $relatedEventsQuery->where('username', $fingerprint['username']);
        }

        // Exact MIN/MAX of all related events
        $agg = (clone $relatedEventsQuery)->selectRaw('MIN(timestamp) as min_ts, MAX(timestamp) as max_ts')->first();
        $firstSeen = $agg->min_ts ? Carbon::parse($agg->min_ts) : $triggerEvent->timestamp;
        $lastSeen = $agg->max_ts ? Carbon::parse($agg->max_ts) : $triggerEvent->timestamp;
        
        // Final risk score formulation
        $baseRisk = $triggerEvent->risk_score;
        $finalRisk = min(100, $baseRisk + $rule->risk_score);

        if ($incident) {
            // Update Existing Incident
            $incident->last_seen_at = $lastSeen;
            if ($firstSeen < $incident->first_seen_at) {
                $incident->first_seen_at = $firstSeen;
            }
            
            // Risk scoring update
            if ($finalRisk > $incident->risk_score) {
                $incident->risk_score = $finalRisk;
            }

            // Escalate rule if it progressed (e.g. USERNAME_ENUMERATION -> PASSWORD_SPRAYING)
            if ($incident->detection_rule !== $ruleName && $rule->risk_score > $incident->risk_score) {
                $incident->detection_rule = $ruleName;
                $incident->title = str_replace('_', ' ', $ruleName);
                $incident->severity = $rule->severity;
            }
            
            // For Spraying/Enumeration, update description to reflect full list of targets
            if (in_array($ruleName, ['PASSWORD_SPRAYING', 'USERNAME_ENUMERATION'])) {
                $incident->description = $description;
            }

            $incident->save();
            
            // Link new/past events
            $triggerEvent->update(['incident_id' => $incident->id]);
            $relatedEventsQuery->whereNull('incident_id')->update(['incident_id' => $incident->id]);
        } else {
            // Create New Incident
            $orgId = 1; // Fallback to org 1
            
            $newIncident = SecurityIncident::create([
                'title' => str_replace('_', ' ', $ruleName),
                'incident_type' => $incidentType,
                'severity' => $rule->severity,
                'workflow_status' => 'reported',
                'edr_status' => 'OPEN',
                'organization_id' => $orgId,
                'ci_id' => $triggerEvent->agent->ci_id ?? null,
                'reporter_id' => 1,
                'description' => $description,
                'risk_score' => $finalRisk,
                'source_ip' => $fingerprint['source_ip'] ?? $triggerEvent->source_ip,
                'username' => $fingerprint['username'] ?? $triggerEvent->username,
                'detection_rule' => $ruleName,
                'first_seen_at' => $firstSeen,
                'last_seen_at' => $lastSeen,
                'agent_id' => $triggerEvent->agent_id,
            ]);

            // Link events
            $triggerEvent->update(['incident_id' => $newIncident->id]);
            $relatedEventsQuery->whereNull('incident_id')->update(['incident_id' => $newIncident->id]);
        }
    }
}

