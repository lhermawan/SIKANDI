<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'cpu_usage',
        'memory_total',
        'memory_used',
        'memory_usage',
        'disk_total',
        'disk_used',
        'disk_usage',
        'rx_bytes',
        'tx_bytes',
        'uptime_seconds',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
