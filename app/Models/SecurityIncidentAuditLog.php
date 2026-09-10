<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityIncidentAuditLog extends Model
{
    protected $fillable = [
        'incident_id', 'user_id', 'action', 
        'old_value', 'new_value', 'ip_address', 'user_agent'
    ];

    public function incident()
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
