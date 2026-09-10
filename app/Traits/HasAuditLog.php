<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::created(function (Model $model) {
            static::recordAuditLog($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getDirty());
            $newValues = $model->getDirty();

            // Exclude updated_at from noisy logs if it's the only change
            unset($oldValues['updated_at'], $newValues['updated_at']);

            if (! empty($newValues)) {
                static::recordAuditLog($model, 'updated', $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            static::recordAuditLog($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function recordAuditLog(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            $user = Auth::user();
            $moduleName = class_basename($model);

            // Map common module names
            $moduleMap = [
                'ConfigurationItem' => 'CMDB',
                'CiRelationship' => 'CMDB',
                'CiType' => 'CMDB',
                'Asset' => 'ITAM',
                'AssetAssignment' => 'ITAM',
                'AssetMaintenance' => 'ITAM',
                'Ticket' => 'ServiceDesk',
                'Incident' => 'IncidentManagement',
                'Website' => 'WebsiteMonitoring',
                'SecurityIncident' => 'CSIRT',
                'Assessment' => 'IKASANDI',
                'Risk' => 'RiskManagement',
                'User' => 'UserManagement',
                'Organization' => 'Organization',
                'Document' => 'DocumentVault',
            ];

            $module = $moduleMap[$moduleName] ?? $moduleName;
            $recordName = $model->name ?? $model->title ?? $model->ci_code ?? $model->asset_number ?? $model->ticket_number ?? ('ID #'.$model->getKey());

            AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'System Worker',
                'action' => $action,
                'module' => $module,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'record_name' => (string) $recordName,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently fail to not block primary business transactions if logging encounters an issue
            report($e);
        }
    }
}
