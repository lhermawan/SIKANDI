<?php

namespace App\Imports;

use App\Models\Website;
use App\Services\WebsiteMonitoringService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WebsitesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['name']) || empty($row['url']) || empty($row['organization_id']) || empty($row['ci_id'])) {
            return null;
        }

        $url = trim((string) $row['url']);
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! WebsiteMonitoringService::isSafePublicUrl($url)) {
            return null;
        }

        return new Website([
            'name' => $row['name'],
            'url' => $url,
            'organization_id' => $row['organization_id'],
            'ci_id' => $row['ci_id'],
        ]);
    }
}
