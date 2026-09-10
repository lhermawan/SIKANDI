<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use GeneratesUniqueCode, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'document_code',
        'title',
        'category',
        'organization_id',
        'ci_id',
        'asset_id',
        'file_path',
        'file_size',
        'version',
        'is_confidential',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_confidential' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Document $doc) {
            if (empty($doc->document_code)) {
                $doc->document_code = static::generateCode('DOC', 'document_code', 4, true);
            }
        });
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
