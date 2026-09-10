<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityIncident extends Model
{
    use GeneratesUniqueCode, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'incident_code',
        'title',
        'incident_type',
        'severity',
        'workflow_status',
        'organization_id',
        'ci_id',
        'reporter_id',
        'assigned_lead_id',
        'description',
        'attack_vector',
        'impact_summary',
        'containment_actions',
        'recovery_actions',
        'evidence_file_path',
        'closed_at',
        'risk_score', 'source_ip', 'username', 'detection_rule',
        'first_seen_at', 'last_seen_at', 'agent_id', 'edr_status',
        // SOC new fields
        'target_type', 'target_value', 'detected_at', 'contained_at', 
        'resolved_at', 'resolution_type', 'resolution_summary', 'root_cause'
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'detected_at' => 'datetime',
            'contained_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SecurityIncident $incident) {
            if (empty($incident->incident_code)) {
                $incident->incident_code = static::generateCode('SEC', 'incident_code', 4, true);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function configurationItem(): BelongsTo
    {
        return $this->belongsTo(ConfigurationItem::class, 'ci_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignedLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_lead_id');
    }

    public function events()
    {
        return $this->hasMany(SecurityEvent::class, 'incident_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    // SOC Relationships
    public function tasks()
    {
        return $this->hasMany(SecurityIncidentTask::class, 'incident_id');
    }

    public function evidence()
    {
        return $this->hasMany(SecurityIncidentEvidence::class, 'incident_id');
    }

    public function responses()
    {
        return $this->hasMany(SecurityIncidentResponse::class, 'incident_id');
    }

    public function assignments()
    {
        return $this->hasMany(SecurityIncidentAssignment::class, 'incident_id');
    }

    public function socAuditLogs()
    {
        return $this->hasMany(SecurityIncidentAuditLog::class, 'incident_id');
    }
}
