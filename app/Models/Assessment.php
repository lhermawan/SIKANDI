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
        'penalty_score',
        'final_score',
        'risk_score',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'compliance_score' => 'decimal:2',
            'penalty_score' => 'decimal:2',
            'final_score' => 'decimal:2',
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
     * Recalculate Compliance Score, Penalty, and Final Score based on facts
     */
    public function recalculateScores(): void
    {
        $answers = $this->answers()->with('question')->get();
        if ($answers->isEmpty()) {
            $this->update(['compliance_score' => 0, 'penalty_score' => 0, 'final_score' => 0, 'risk_score' => 0]);

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

        // Hitung Penalty (Fakta Lapangan)
        $penalty = 0;

        // 1. Kurangi 5 poin untuk setiap insiden aktif
        $openIncidentsCount = SecurityIncident::where('organization_id', $this->organization_id)
            ->where('workflow_status', '!=', 'closed')
            ->count();
        $penalty += ($openIncidentsCount * 5);

        // 2. Kurangi 2 poin untuk setiap website yang down atau SSL mati
        $websiteIssues = Website::where('organization_id', $this->organization_id)
            ->where(function ($q) {
                $q->where('current_status', 'down')
                    ->orWhere('ssl_status', 'expired');
            })->count();
        $penalty += ($websiteIssues * 2);

        // Maksimal penalti misal 100 agar skor tidak minus ekstrim
        $penalty = min($penalty, 100);

        // Final score (jangan sampai minus)
        $finalScore = max(0, $compliance - $penalty);
        $risk = round(100 - $finalScore, 2);

        $this->update([
            'compliance_score' => $compliance,
            'penalty_score' => $penalty,
            'final_score' => $finalScore,
            'risk_score' => $risk,
        ]);
    }
}
