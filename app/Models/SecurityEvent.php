<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $fillable = [
        'event_id', 'agent_id', 'timestamp', 'event_type', 'action',
        'hostname', 'username', 'source_ip', 'process', 'severity',
        'risk_score', 'reason', 'metadata', 'incident_id'
    ];

    protected $casts = [
        'metadata' => 'array',
        'timestamp' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function incident()
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }
}
