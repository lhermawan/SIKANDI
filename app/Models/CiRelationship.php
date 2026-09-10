<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CiRelationship extends Model
{
    use HasAuditLog, HasFactory;

    protected $fillable = [
        'source_ci_id',
        'target_ci_id',
        'relationship_type',
        'description',
    ];

    public function sourceCi(): BelongsTo
    {
        return $this->belongsTo(ConfigurationItem::class, 'source_ci_id');
    }

    public function targetCi(): BelongsTo
    {
        return $this->belongsTo(ConfigurationItem::class, 'target_ci_id');
    }

    public function getHumanTypeAttribute(): string
    {
        return match ($this->relationship_type) {
            'depends_on' => 'Depends On (Bergantung Pada)',
            'used_by' => 'Used By (Digunakan Oleh)',
            'hosted_on' => 'Hosted On (Dihosting Pada)',
            'runs_on' => 'Runs On (Berjalan Di Atas)',
            'connects_to' => 'Connects To (Terhubung Ke)',
            'contains' => 'Contains (Berisi / Memuat)',
            'uses' => 'Uses (Menggunakan)',
            'protected_by' => 'Protected By (Dilindungi Oleh)',
            'managed_by' => 'Managed By (Dikelola Oleh)',
            'located_at' => 'Located At (Berlokasi Di)',
            'owned_by' => 'Owned By (Dimiliki Oleh)',
            'supports' => 'Supports (Mendukung)',
            'part_of' => 'Part Of (Bagian Dari)',
            default => ucfirst(str_replace('_', ' ', $this->relationship_type)),
        };
    }

    public function getInverseHumanTypeAttribute(): string
    {
        return match ($this->relationship_type) {
            'depends_on' => 'Required By (Dibutuhkan Oleh)',
            'used_by' => 'Uses (Menggunakan)',
            'hosted_on' => 'Hosts (Menampung / Menghosting)',
            'runs_on' => 'Executes (Menjalankan)',
            'connects_to' => 'Connected From (Terhubung Dari)',
            'contains' => 'Contained In (Bagian Di Dalam)',
            'uses' => 'Used By (Digunakan Oleh)',
            'protected_by' => 'Protects (Melindungi)',
            'managed_by' => 'Manages (Mengelola)',
            'located_at' => 'Houses (Tempat Untuk)',
            'owned_by' => 'Owns (Memiliki)',
            'supports' => 'Supported By (Didukung Oleh)',
            'part_of' => 'Composed Of (Terdiri Dari)',
            default => 'Inverse: '.ucfirst(str_replace('_', ' ', $this->relationship_type)),
        };
    }
}
