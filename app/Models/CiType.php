<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CiType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'icon',
        'color',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function configurationItems(): HasMany
    {
        return $this->hasMany(ConfigurationItem::class);
    }
}
