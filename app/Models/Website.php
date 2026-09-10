<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    use HasAuditLog, HasFactory;

    protected $fillable = [
        'ci_id',
        'organization_id',
        'name',
        'url',
        'check_interval_minutes',
        'current_status',
        'http_status_code',
        'response_time_ms',
        'ip_address',
        'ssl_status',
        'ssl_issuer',
        'ssl_expires_at',
        'last_checked_at',
        'last_status_change_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ssl_expires_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'last_status_change_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function configurationItem(): BelongsTo
    {
        return $this->belongsTo(ConfigurationItem::class, 'ci_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebsiteCheckLog::class)->orderBy('checked_at', 'desc');
    }
}
