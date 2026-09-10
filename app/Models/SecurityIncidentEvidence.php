<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityIncidentEvidence extends Model
{
    protected $table = 'security_incident_evidence';
    
    protected $fillable = [
        'incident_id', 'type', 'title', 'description', 
        'file_path', 'file_hash', 'mime_type', 'file_size', 
        'collected_by', 'collected_at', 'metadata'
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function incident()
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
