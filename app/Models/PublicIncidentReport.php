<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicIncidentReport extends Model
{
    use HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'source',
        'whatsapp_from',
        'reporter_name',
        'reporter_contact',
        'incident_type',
        'incident_time',
        'affected_asset',
        'chronology',
        'impact',
        'evidence_note',
        'attachments',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'security_incident_id',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'incident_time' => 'datetime',
            'reviewed_at' => 'datetime',
            'attachments' => 'array',
            'raw_payload' => 'array',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function securityIncident(): BelongsTo
    {
        return $this->belongsTo(SecurityIncident::class, 'security_incident_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_review';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
