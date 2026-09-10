<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityIncidentResponse extends Model
{
    protected $fillable = [
        'incident_id', 'action', 'description', 'status', 
        'performed_by', 'performed_at', 'result', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
        ];
    }

    public function incident()
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
