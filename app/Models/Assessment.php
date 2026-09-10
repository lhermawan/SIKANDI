<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use GeneratesUniqueCode, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'assessment_code',
        'title',
        'year',
        'period',
        'organization_id',
        'assessor_id',
        'status',
        'compliance_score',
        'risk_score',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'compliance_score' => 'decimal:2',
            'risk_score' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Assessment $assessment) {
            if (empty($assessment->assessment_code)) {
                $assessment->assessment_code = static::generateCode('ASM', 'assessment_code', 3, true);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    /**
     * Recalculate Compliance Score and Risk Score based on answers
     */
    public function recalculateScores(): void
    {
        $answers = $this->answers()->with('question')->get();
        if ($answers->isEmpty()) {
            $this->update(['compliance_score' => 0, 'risk_score' => 0]);

            return;
        }

        $totalScore = 0;
        $maxPossible = 0;

        foreach ($answers as $ans) {
            if ($ans->answer === 'not_applicable') {
                continue;
            }
            $maxPossible += 100;
            $totalScore += match ($ans->answer) {
                'compliant' => 100,
                'partial' => 50,
                default => 0,
            };
        }

        $compliance = $maxPossible > 0 ? round(($totalScore / $maxPossible) * 100, 2) : 0;
        $risk = round(100 - $compliance, 2);

        $this->update([
            'compliance_score' => $compliance,
            'risk_score' => $risk,
        ]);
    }
}
