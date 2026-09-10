<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_answer_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'description',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(AssessmentAnswer::class, 'assessment_answer_id');
    }
}
