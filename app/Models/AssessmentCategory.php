<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'weight',
        'order_num',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'order_num' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class, 'category_id')->orderBy('order_num', 'asc');
    }
}
