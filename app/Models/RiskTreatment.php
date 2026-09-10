<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskTreatment extends Model
{
    use HasAuditLog, HasFactory;

    protected $fillable = [
        'risk_id',
        'strategy',
        'action_plan',
        'assigned_to',
        'progress_percent',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_percent' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
