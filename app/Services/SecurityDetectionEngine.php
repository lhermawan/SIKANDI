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
            'timestamp' => isset($payload['timestamp']) && is_numeric($payload['timestamp'])
    ? Carbon::createFromTimestamp($payload['timestamp'], 'Asia/Jakarta')
    : now(),
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

    /**
     * Build a query scope that matches strictly RAW "login failed" events.
     * EXCLUDES detection/correlation events like "brute_force detected".
     */
    protected function queryLoginFailed($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereIn('event_type', ['login', 'authentication', 'auth'])
                      ->whereIn('action', ['failed', 'login_failed', 'failure', 'denied']);
            })->orWhereIn('event_type', [
                'loginfailed', 'login_failed', 'failed_login',
                'LOGINFAILED', 'LOGIN_FAILED', 'FAILED_LOGIN',
            ]);
        })->where('event_type', '!=', 'brute_force')
          ->where('event_type', '!=', 'correlation');
    }

    protected function queryLoginSuccess($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereIn('event_type', ['login', 'authentication', 'auth'])
                      ->whereIn('action', ['success', 'login_success', 'accepted', 'ok']);
            })->orWhereIn('event_type', ['loginsuccess', 'login_success', 'success_login']);
        });
    }

    protected function detectBruteForce(SecurityEvent $event, SecurityRule $rule)
    {
        $cls = $this->classifyEvent($event);
        if (! $cls['is_login_fail']) return;
        
        // Ensure this is a raw event, not a prior detection feedback loop
        if ($event->event_type === 'correlation' || $event->event_type === 'brute_force') return;

        if (empty($event->source_ip) || empty($event->username)) return;

        // Detection Window (e.g., 300 seconds)
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
                description: "", // Will be dynamically generated
                incidentType: 'unauthorized_access',
                fingerprint: ['source_ip' => $event->source_ip, 'username' => $event->username]
            );
        }
    }

    protected function detectUsernameEnumeration(SecurityEvent $event, SecurityRule $rule)
    {
        $cls = $this->classifyEvent($event);
        if (! $cls['is_login_fail']) return;
        if ($event->event_type === 'correlation' || $event->event_type === 'brute_force') return;
        if (empty($event->source_ip)) return;

        $since = Carbon::parse($event->timestamp)->subSeconds($rule->time_window_seconds);

        $events = $this->queryLoginFailed(
            SecurityEvent::where('agent_id', $event->agent_id)
                ->where('source_ip', $event->source_ip)
                ->where('timestamp', '>=', $since)
        )->get();

        $uniqueUsernames = $events->pluck('username')->filter()->unique();
        $uniqueCount = $uniqueUsernames->count();

        if ($uniqueCount >= $rule->threshold) {
            $sprayingRule = SecurityRule::where('name', 'PASSWORD_SPRAYING')->where('enabled', true)->first();
            $escalateToSpraying = $sprayingRule && $uniqueCount >= $sprayingRule->threshold;
            
            $activeRule = $escalateToSpraying ? $sprayingRule : $rule;
            
            $this->createOrUpdateIncident(
                triggerEvent: $event,
                rule: $activeRule,
                ruleName: $activeRule->name,
                description: "", // Dynamically generated
                incidentType: 'unauthorized_access',
                fingerprint: ['source_ip' => $event->source_ip],
                targetUsernames: $uniqueUsernames->toArray()
            );
        }
    }

    protected function detectPasswordSpraying(SecurityEvent $event, SecurityRule $rule)
    {
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
                description: "", // Dynamically generated
                incidentType: 'account_compromise',
                fingerprint: ['source_ip' => $event->source_ip, 'username' => $event->username]
            );
        }
    }

    protected function createOrUpdateIncident(SecurityEvent $triggerEvent, SecurityRule $rule, $ruleName, $description, $incidentType, $fingerprint = [], $targetUsernames = [])
    {
        if (!$rule->auto_incident) return;

        // 1. INCIDENT LIFETIME DEDUPLICATION
        // We look for any OPEN incident for this campaign regardless of the strict 300s detection window.
        // As long as the incident isn't closed, it's the same campaign.
        $query = SecurityIncident::whereIn('detection_rule', [$ruleName, 'USERNAME_ENUMERATION', 'PASSWORD_SPRAYING'])
            ->where('agent_id', $triggerEvent->agent_id)
            ->whereNotIn('workflow_status', ['closed', 'resolved', 'false_positive']);
            
        if (isset($fingerprint['source_ip'])) {
            $query->where('source_ip', $fingerprint['source_ip']);
        }
        if (isset($fingerprint['username']) && $ruleName !== 'PASSWORD_SPRAYING' && $ruleName !== 'USERNAME_ENUMERATION') {
            $query->where('username', $fingerprint['username']);
        }
        
        $incident = $query->first(); // Get the existing active campaign

        // 2. GATHER ALL RAW EVENTS FOR THIS CAMPAIGN
        $relatedEventsQuery = SecurityEvent::where('agent_id', $triggerEvent->agent_id);
        if ($incident) {
            // Include events already linked + new ones from this source
            $relatedEventsQuery->where(function($q) use ($incident, $fingerprint, $triggerEvent, $ruleName) {
                $q->where('incident_id', $incident->id)
                  ->orWhere(function($sub) use ($fingerprint, $triggerEvent, $ruleName) {
                      $sub->whereNull('incident_id')
                          ->where('timestamp', '>=', Carbon::parse($triggerEvent->timestamp)->subHours(24));
                      if (isset($fingerprint['source_ip'])) $sub->where('source_ip', $fingerprint['source_ip']);
                      if (isset($fingerprint['username']) && !in_array($ruleName, ['PASSWORD_SPRAYING', 'USERNAME_ENUMERATION'])) {
                          $sub->where('username', $fingerprint['username']);
                      }
                  });
            });
        } else {
            // New campaign, gather events in recent window
            $relatedEventsQuery->whereNull('incident_id')
                ->where('timestamp', '>=', Carbon::parse($triggerEvent->timestamp)->subHours(24));
            if (isset($fingerprint['source_ip'])) $relatedEventsQuery->where('source_ip', $fingerprint['source_ip']);
            if (isset($fingerprint['username']) && !in_array($ruleName, ['PASSWORD_SPRAYING', 'USERNAME_ENUMERATION'])) {
                $relatedEventsQuery->where('username', $fingerprint['username']);
            }
        }

        // Only aggregate RAW events for stats
        $rawEventsQuery = (clone $relatedEventsQuery)->where('event_type', '!=', 'correlation')->where('event_type', '!=', 'brute_force');
        $rawLoginsQuery = $this->queryLoginFailed(clone $rawEventsQuery);
        
        $rawCount = $rawLoginsQuery->count();
        $detectionCount = (clone $relatedEventsQuery)->where(function($q) {
            $q->where('event_type', 'correlation')->orWhere('event_type', 'brute_force');
        })->count();
        $totalCount = $relatedEventsQuery->count();

        // 3. EXACT FIRST/LAST SEEN FROM RAW EVENTS
        $agg = (clone $rawEventsQuery)->selectRaw('MIN(timestamp) as min_ts, MAX(timestamp) as max_ts')->first();
        $firstSeen = $agg->min_ts ? Carbon::parse($agg->min_ts) : $triggerEvent->timestamp;
        $lastSeen = $agg->max_ts ? Carbon::parse($agg->max_ts) : $triggerEvent->timestamp;

        // Update target usernames for spraying
        if (empty($targetUsernames) && in_array($ruleName, ['PASSWORD_SPRAYING', 'USERNAME_ENUMERATION'])) {
            $targetUsernames = $rawLoginsQuery->pluck('username')->filter()->unique()->toArray();
        }

        // 4. RISK SCORING (Explainable)
        $riskScore = $rule->risk_score;
        $riskFactors = ["{$ruleName} rule bonus" => "+{$rule->risk_score}"];
        
        // Check for evidence: Account Compromise (Successful login after failures)
        $hasSuccess = $this->queryLoginSuccess(clone $rawEventsQuery)->exists();
        if ($hasSuccess) {
            $riskScore += 30;
            $riskFactors["Successful login detected"] = "+30";
        }
        
        // Check for Privilege Escalation
        $hasPrivEsc = (clone $rawEventsQuery)->where('event_type', 'privilege_escalation')->exists();
        if ($hasPrivEsc) {
            $riskScore += 40;
            $riskFactors["Privilege escalation"] = "+40";
        }

        $finalRisk = min(100, max(0, $riskScore));

        // 5. BUILD DESCRIPTION
        $descLines = [];
        if (in_array($ruleName, ['PASSWORD_SPRAYING', 'USERNAME_ENUMERATION'])) {
            $uniqueCount = count($targetUsernames);
            $descLines[] = "Detected login attempts against {$uniqueCount} unique usernames from IP " . ($fingerprint['source_ip'] ?? 'unknown') . ".";
            $descLines[] = "\nTarget Usernames:";
            foreach ($targetUsernames as $u) {
                $descLines[] = "- {$u}";
            }
        } else {
            $descLines[] = "Multiple failed authentication attempts detected for user " . ($fingerprint['username'] ?? 'unknown') . " from IP " . ($fingerprint['source_ip'] ?? 'unknown') . ".";
        }

        $descLines[] = "\n=== Attack Summary ===";
        $descLines[] = "Raw Login Attempts : {$rawCount}";
        $descLines[] = "Detection Events   : {$detectionCount}";
        $descLines[] = "Total Related      : {$totalCount}";
        
        $descLines[] = "\n=== Risk Explanation ===";
        $descLines[] = "Final Risk Score   : {$finalRisk} / 100";
        foreach ($riskFactors as $factor => $val) {
            $descLines[] = str_pad($factor, 26) . " " . $val;
        }

        $finalDescription = implode("\n", $descLines);

        // 6. SAVE INCIDENT
        if ($incident) {
            $incident->last_seen_at = $lastSeen;
            if ($firstSeen < $incident->first_seen_at) $incident->first_seen_at = $firstSeen;
            
            $incident->risk_score = $finalRisk; // Do not use MAX(), use exactly calculated risk from evidence
            $incident->description = $finalDescription;

            if ($incident->detection_rule !== $ruleName && $rule->risk_score > SecurityRule::where('name', $incident->detection_rule)->value('risk_score')) {
                $incident->detection_rule = $ruleName;
                $incident->title = str_replace('_', ' ', $ruleName);
                $incident->severity = $rule->severity;
            }
            
            // Only update severity if it's strictly increasing or adapting to the current rule config
            if ($rule->severity === 'critical') $incident->severity = 'critical';

            $incident->save();
            
            // Attach all matched events
            $triggerEvent->update(['incident_id' => $incident->id]);
            (clone $relatedEventsQuery)->whereNull('incident_id')->update(['incident_id' => $incident->id]);
        } else {
            $newIncident = SecurityIncident::create([
                'title' => str_replace('_', ' ', $ruleName),
                'incident_type' => $incidentType,
                'severity' => $rule->severity,
                'workflow_status' => 'reported',
                'edr_status' => 'OPEN',
                'organization_id' => 1,
                'ci_id' => $triggerEvent->agent->ci_id ?? null,
                'reporter_id' => 1,
                'description' => $finalDescription,
                'risk_score' => $finalRisk,
                'source_ip' => $fingerprint['source_ip'] ?? $triggerEvent->source_ip,
                'username' => $fingerprint['username'] ?? $triggerEvent->username,
                'detection_rule' => $ruleName,
                'first_seen_at' => $firstSeen,
                'last_seen_at' => $lastSeen,
                'agent_id' => $triggerEvent->agent_id,
            ]);

            $triggerEvent->update(['incident_id' => $newIncident->id]);
            (clone $relatedEventsQuery)->whereNull('incident_id')->update(['incident_id' => $newIncident->id]);
        }
    }

}

