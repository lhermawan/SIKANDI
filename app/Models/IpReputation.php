<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpReputation extends Model
{
    protected $fillable = [
        'ip_address', 'is_whitelisted', 'is_public', 'abuse_confidence_score',
        'country_code', 'usage_type', 'isp', 'domain',
        'total_reports', 'raw_data', 'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'is_public' => 'boolean',
            'is_whitelisted' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    public function isMalicious(): bool
    {
        return $this->abuse_confidence_score >= 80;
    }

    public function isSuspicious(): bool
    {
        return $this->abuse_confidence_score >= 20 && $this->abuse_confidence_score < 80;
    }
}
