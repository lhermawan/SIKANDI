<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ConfigurationItem extends Model
{
    use GeneratesUniqueCode, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'ci_code',
        'name',
        'ci_type_id',
        'asset_id',
        'organization_id',
        'location_id',
        'hostname',
        'ip_address',
        'mac_address',
        'domain',
        'url',
        'manufacturer',
        'model',
        'serial_number',
        'operating_system',
        'os_version',
        'environment',
        'status',
        'criticality',
        'owner_person',
        'responsible_unit',
        'specifications',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ConfigurationItem $ci) {
            if (empty($ci->ci_code)) {
                $typeCode = $ci->ciType?->code ?? 'GEN';
                $prefix = 'CI-'.strtoupper(substr($typeCode, 0, 3));
                $ci->ci_code = static::generateCode($prefix, 'ci_code', 5, false);
            }
        });
    }

    public function ciType(): BelongsTo
    {
        return $this->belongsTo(CiType::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function outboundRelations(): HasMany
    {
        return $this->hasMany(CiRelationship::class, 'source_ci_id');
    }

    public function inboundRelations(): HasMany
    {
        return $this->hasMany(CiRelationship::class, 'target_ci_id');
    }

    public function website(): HasOne
    {
        return $this->hasOne(Website::class, 'ci_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'ci_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'ci_id');
    }

    public function securityIncidents(): HasMany
    {
        return $this->hasMany(SecurityIncident::class, 'ci_id');
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class, 'ci_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'ci_id');
    }

    /**
     * Get unified collection of relationships with direction and logical inverse label
     */
    public function getAllRelationships(): Collection
    {
        $outbound = $this->outboundRelations()
            ->with(['targetCi.ciType'])
            ->get()
            ->map(function ($rel) {
                return [
                    'id' => $rel->id,
                    'direction' => 'outbound',
                    'relation_type' => $rel->relationship_type,
                    'display_label' => $rel->human_type,
                    'related_ci' => $rel->targetCi,
                    'description' => $rel->description,
                    'created_at' => $rel->created_at,
                ];
            });

        $inbound = $this->inboundRelations()
            ->with(['sourceCi.ciType'])
            ->get()
            ->map(function ($rel) {
                return [
                    'id' => $rel->id,
                    'direction' => 'inbound',
                    'relation_type' => $rel->relationship_type,
                    'display_label' => $rel->inverse_human_type,
                    'related_ci' => $rel->sourceCi,
                    'description' => $rel->description,
                    'created_at' => $rel->created_at,
                ];
            });

        return $outbound->concat($inbound);
    }
}
