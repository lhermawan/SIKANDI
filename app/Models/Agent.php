<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'ci_id',
        'agent_id',
        'hostname',
        'ip_address',
        'os',
        'os_version',
        'agent_version',
        'status',
        'last_seen_at',
        'registered_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function configurationItem(): BelongsTo
    {
        return $this->belongsTo(ConfigurationItem::class, 'ci_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(AgentMetric::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(AgentService::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AgentEvent::class);
    }
}
