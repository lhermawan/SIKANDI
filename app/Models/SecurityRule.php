<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityRule extends Model
{
    protected $fillable = [
        'name', 'enabled', 'threshold', 'time_window_seconds',
        'severity', 'risk_score', 'auto_incident'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'auto_incident' => 'boolean',
    ];
}
