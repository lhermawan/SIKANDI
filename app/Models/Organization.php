<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'address',
        'phone',
        'email',
        'website_url',
        'head_name',
        'head_nip',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function configurationItems(): HasMany
    {
        return $this->hasMany(ConfigurationItem::class);
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }
}
