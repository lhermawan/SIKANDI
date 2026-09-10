<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Risk extends Model
{
    use GeneratesUniqueCode, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'risk_code',
        'title',
        'description',
        'organization_id',
        'ci_id',
        'asset_id',
        'threat',
        'vulnerability',
        'likelihood',
        'impact',
        'risk_score',
        'risk_level',
        'owner_id',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'likelihood' => 'integer',
            'impact' => 'integer',
            'risk_score' => 'integer',
            'due_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Risk $risk) {
            if (empty($risk->risk_code)) {
                $risk->risk_code = static::generateCode('RSK', 'risk_code', 3, true);
            }
            $risk->calculateRiskScore();
        });

        static::updating(function (Risk $risk) {
            $risk->calculateRiskScore();
        });
    }

    public function calculateRiskScore(): void
    {
        $this->risk_score = max(1, min(5, $this->likelihood)) * max(1, min(5, $this->impact));
        $this->risk_level = match (true) {
            $this->risk_score >= 16 => 'critical',
            $this->risk_score >= 10 => 'high',
            $this->risk_score >= 5 => 'medium',
            default => 'low',
        };
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function configurationItem(): BelongsTo
    {
        return $this->belongsTo(ConfigurationItem::class, 'ci_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(RiskTreatment::class);
    }
}
