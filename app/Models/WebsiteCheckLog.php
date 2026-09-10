<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteCheckLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'website_id',
        'status',
        'http_status_code',
        'response_time_ms',
        'error_message',
        'ssl_valid',
        'ssl_days_left',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'ssl_valid' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
