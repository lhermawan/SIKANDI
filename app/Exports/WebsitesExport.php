<?php

namespace App\Exports;

use App\Models\Website;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WebsitesExport implements FromQuery, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query(): Builder|EloquentBuilder|Relation
    {
        $query = Website::query()->with(['configurationItem', 'organization'])->latest('last_checked_at');

        if ($search = $this->request->input('search')) {
            $query->where(function ($q) use ($search) {
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
            'IP Address',
            'OPD Pengelola',
            'CI Terkait',
            'Status Saat Ini',
            'Keterangan Error',
            'HTTP Status',
            'Response Time (ms)',
            'Status SSL',
            'Masa Aktif SSL (Hari)',
            'Terakhir Dicek',
        ];
    }

    private function sanitizeFormula(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $dangerousChars = ['=', '+', '-', '@', "\t", "\r"];
        if (in_array($value[0], $dangerousChars, true)) {
            return "'".$value;
        }

        return $value;
    }

    public function map($site): array
    {
        $sslDays = null;
        if ($site->ssl_expires_at) {
            $sslDays = round(now()->diffInDays($site->ssl_expires_at, false));
        }

        return [
            $this->sanitizeFormula($site->name),
            $this->sanitizeFormula($site->url),
            $site->ip_address,
            $this->sanitizeFormula($site->organization ? $site->organization->name : ''),
            $site->configurationItem ? $site->configurationItem->ci_code : '',
            strtoupper($site->current_status),
            $this->sanitizeFormula($site->current_status === 'down' ? $site->last_error : ''),
            $site->http_status_code,
            $site->response_time_ms,
            strtoupper($site->ssl_status),
            $sslDays,
            $site->last_checked_at ? $site->last_checked_at->format('Y-m-d H:i:s') : '',
        ];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul Laporan
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'LAPORAN HASIL MONITORING WEBSITE & SSL (SIKANDI)');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Tanggal Export
                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'Tanggal Export: '.now()->translatedFormat('d F Y H:i:s'));
                $sheet->getStyle('A2')->getFont()->setItalic(true);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Styling untuk Headings (Baris ke-4)
                $sheet->getStyle('A4:L4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0F172A'], // Slate 900
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Menambahkan Border ke seluruh data
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A4:L'.$highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF64748B'], // Slate 500
                        ],
                    ],
                ]);

                // Auto-filter untuk kolom-kolom tabel
                $sheet->setAutoFilter('A4:L'.$highestRow);
            },
        ];
    }
}
