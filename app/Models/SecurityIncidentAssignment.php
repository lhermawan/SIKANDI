<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityIncidentAssignment extends Model
{
    protected $fillable = [
        'incident_id', 'user_id', 'assigned_by', 
        'assigned_at', 'unassigned_at'
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    public function incident()
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
