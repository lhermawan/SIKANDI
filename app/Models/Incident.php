<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use GeneratesUniqueCode, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'incident_number',
        'title',
        'source',
        'ticket_id',
        'ci_id',
        'asset_id',
        'organization_id',
        'assigned_technician_id',
        'priority',
        'status',
        'impact_description',
        'root_cause',
        'resolution',
        'detected_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Incident $incident) {
            if (empty($incident->incident_number)) {
                $incident->incident_number = static::generateCode('INC', 'incident_number', 4, true);
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function configurationItem(): BelongsTo
    {
        return $this->belongsTo(ConfigurationItem::class, 'ci_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IncidentComment::class)->orderBy('created_at', 'asc');
    }
}
