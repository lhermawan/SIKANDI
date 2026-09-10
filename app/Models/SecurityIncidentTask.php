<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityIncidentTask extends Model
{
    protected $fillable = [
        'incident_id', 'category', 'task', 'description', 
        'status', 'checked_by', 'checked_at', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function incident()
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
