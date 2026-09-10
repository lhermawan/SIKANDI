<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Asset extends Model
{
    use GeneratesUniqueCode, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_number',
        'name',
        'asset_category_id',
        'vendor_id',
        'organization_id',
        'location_id',
        'assigned_to_user_id',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'warranty_expiry_date',
        'lifecycle_status',
        'condition',
        'qr_code_token',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry_date' => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (empty($asset->asset_number)) {
                $asset->asset_number = static::generateCode('AST', 'asset_number', 4, true);
            }
            if (empty($asset->qr_code_token)) {
                $asset->qr_code_token = 'AST-'.Str::upper(Str::random(12));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function configurationItems(): HasMany
    {
        return $this->hasMany(ConfigurationItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->orderBy('assigned_at', 'desc');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->orderBy('start_date', 'desc');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}
