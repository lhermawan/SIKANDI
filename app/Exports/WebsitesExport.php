<?php

namespace App\Exports;

use App\Models\Website;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WebsitesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Website::query()->with(['configurationItem', 'organization'])->latest('last_checked_at');

        if ($search = $this->request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }
        if ($status = $this->request->input('status')) {
            if ($status === 'ssl_warning') {
                $query->whereIn('ssl_status', ['expiring_soon', 'expired', 'invalid']);
            } else {
                $query->where('current_status', $status);
            }
        }
        if ($opd = $this->request->input('organization_id')) {
            $query->where('organization_id', $opd);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Website',
            'URL',
            'OPD Pengelola',
            'CI Terkait',
            'Status Saat Ini',
            'HTTP Status',
            'Response Time (ms)',
            'Status SSL',
            'Masa Aktif SSL (Hari)',
            'Terakhir Dicek'
        ];
    }

    public function map($site): array
    {
        $sslDays = null;
        if ($site->ssl_expires_at) {
            $sslDays = round(now()->diffInDays($site->ssl_expires_at, false));
        }

        return [
            $site->name,
            $site->url,
            $site->organization ? $site->organization->name : '',
            $site->configurationItem ? $site->configurationItem->ci_code : '',
            strtoupper($site->current_status),
            $site->http_status_code,
            $site->response_time_ms,
            strtoupper($site->ssl_status),
            $sslDays,
            $site->last_checked_at ? $site->last_checked_at->format('Y-m-d H:i:s') : ''
        ];
    }
}
