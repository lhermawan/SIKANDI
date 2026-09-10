<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'question',
        'explanation',
        'guidance',
        'max_score',
        'is_active',
        'order_num',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'is_active' => 'boolean',
            'order_num' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'category_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class, 'question_id');
    }
}
