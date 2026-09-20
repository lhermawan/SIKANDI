<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommand extends Model
{
    use HasUuids;

    protected $fillable = [
        'agent_id',
        'action',
        'paths',
        'status',
        'result',
    ];

    protected $casts = [
        'paths' => 'array',
        'result' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
