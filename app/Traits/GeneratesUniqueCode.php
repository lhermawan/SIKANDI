<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait GeneratesUniqueCode
{
    /**
     * Generate sequential code with prefix and year e.g. CI-SRV-00001, AST-2026-0001, TKT-2026-0001
     */
    public static function generateCode(string $prefix, string $column = 'code', int $digits = 4, bool $includeYear = false): string
    {
        $year = date('Y');
        $fullPrefix = $includeYear ? "{$prefix}-{$year}-" : "{$prefix}-";

        $query = static::query();
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            $query->withTrashed();
        }

        $latest = $query->where($column, 'like', "{$fullPrefix}%")
            ->orderBy('id', 'desc')
            ->value($column);

        if (! $latest) {
            $number = 1;
        } else {
            $currentNum = (int) substr($latest, strlen($fullPrefix));
            $number = $currentNum + 1;
        }

        do {
            $candidate = $fullPrefix.str_pad((string) $number, $digits, '0', STR_PAD_LEFT);
            $check = static::query();
            if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
                $check->withTrashed();
            }
            $exists = $check->where($column, $candidate)->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $candidate;
    }
}
