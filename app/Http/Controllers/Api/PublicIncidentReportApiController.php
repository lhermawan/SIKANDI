<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicIncidentReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicIncidentReportApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->authorizeBot($request);

        $data = $request->validate([
            'ticket_number' => ['nullable', 'string', 'max:50'],
            'whatsapp_from' => ['required', 'string', 'max:80'],
            'data' => ['required', 'array'],
            'data.nama' => ['nullable', 'string', 'max:255'],
            'data.kontak' => ['nullable', 'string', 'max:255'],
            'data.jenis' => ['nullable', 'string', 'max:255'],
            'data.waktu' => ['nullable', 'string', 'max:255'],
            'data.lokasi' => ['nullable', 'string', 'max:255'],
            'data.kronologi' => ['nullable', 'string'],
            'data.dampak' => ['nullable', 'string'],
            'data.bukti' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.mimeType' => ['nullable', 'string', 'max:255'],
            'attachments.*.filename' => ['nullable', 'string', 'max:255'],
            'attachments.*.sizeBase64' => ['nullable', 'integer'],
            'attachments.*.capturedAt' => ['nullable', 'string', 'max:255'],
            'attachments.*.data' => ['nullable', 'string'],
            'attachments.*.downloadFailed' => ['nullable', 'boolean'],
            'attachments.*.downloadAttempts' => ['nullable', 'integer'],
            'attachments.*.downloadError' => ['nullable', 'string'],
            'attachments.*.type' => ['nullable', 'string', 'max:80'],
            'attachments.*.note' => ['nullable', 'string'],
        ]);

        $ticketNumber = $data['ticket_number'] ?? $this->generateTicketNumber();

        $report = PublicIncidentReport::create([
            'ticket_number' => $ticketNumber,
            'source' => 'whatsapp',
            'whatsapp_from' => $data['whatsapp_from'],
            'reporter_name' => $data['data']['nama'] ?? null,
            'reporter_contact' => $data['data']['kontak'] ?? ($data['whatsapp_from'] ?? null),
            'incident_type' => $data['data']['jenis'] ?? null,
            'incident_time' => $this->parseIncidentTime($data['data']['waktu'] ?? null),
            'affected_asset' => $data['data']['lokasi'] ?? null,
            'chronology' => $data['data']['kronologi'] ?? null,
            'impact' => $data['data']['dampak'] ?? null,
            'evidence_note' => $data['data']['bukti'] ?? null,
            'attachments' => $this->storeAttachments($data['attachments'] ?? []),
            'status' => 'pending_review',
            'raw_payload' => $this->payloadWithoutAttachmentData($request->all()),
        ]);

        Log::info('Public incident report received in SIKANDI from WhatsApp', [
            'ticket_number' => $report->ticket_number,
            'whatsapp_from' => $report->whatsapp_from,
            'incident_type' => $report->incident_type,
        ]);

        return response()->json([
            'success' => true,
            'ticket_number' => $report->ticket_number,
            'status' => 'Diterima',
            'stage' => 'Menunggu Verifikasi Tim Persandian',
        ], 201);
    }

    public function show(Request $request, string $ticketNumber): JsonResponse
    {
        $this->authorizeBot($request);

        $report = PublicIncidentReport::query()
            ->with('securityIncident')
            ->where('ticket_number', $ticketNumber)
            ->first();

        if (! $report) {
            return response()->json(['success' => false, 'message' => 'Tiket laporan tidak ditemukan di SIKANDI.'], 404);
        }

        $sec = $report->securityIncident;

        $statusText = match ($report->status) {
            'pending_review' => 'Menunggu Verifikasi',
            'rejected' => 'Ditolak (Tidak Valid / Spam)',
            'verified' => $sec ? ($sec->workflow_status ?? 'Terverifikasi (Penanganan SOC)') : 'Terverifikasi',
            default => $report->status,
        };

        $stageText = match ($report->status) {
            'pending_review' => 'Laporan masuk sedang diverifikasi oleh Tim Persandian SIKANDI',
            'rejected' => 'Laporan ditolak: '.($report->review_notes ?: 'Data tidak valid / bukan insiden'),
            'verified' => $sec ? 'Insiden Terkonfirmasi & Sedang Ditangani (Kode: '.$sec->incident_code.')' : 'Laporan Valid (Siap Ditindaklanjuti)',
            default => 'Dalam Penanganan',
        };

        return response()->json([
            'success' => true,
            'ticket_number' => $report->ticket_number,
            'status' => $statusText,
            'stage' => $stageText,
            'security_incident_code' => $sec?->incident_code,
            'insiden_siber_ticket_number' => $sec?->incident_code,
            'updated_at' => ($sec?->updated_at ?: $report->updated_at)?->toISOString(),
        ]);
    }

    private function authorizeBot(Request $request): void
    {
        $token = (string) (config('services.whatsapp.token') ?: env('WA_BOT_SECRET', ''));

        if ($token === '' || ! hash_equals($token, (string) $request->bearerToken())) {
            abort(401, 'Unauthorized access to WhatsApp bot endpoint');
        }
    }

    private function storeAttachments(array $attachments): array
    {
        $maxSizeBytes = 10 * 1024 * 1024; // 10 MB limit per attachment

        return collect($attachments)->map(function (array $attachment) use ($maxSizeBytes): array {
            $data = $attachment['data'] ?? null;

            if (is_string($data) && $data !== '') {
                $binary = base64_decode($data, true);

                if ($binary !== false) {
                    if (strlen($binary) > $maxSizeBytes) {
                        $attachment['downloadFailed'] = true;
                        $attachment['note'] = 'Ukuran lampiran melebihi batas maksimal (10 MB).';
                    } else {
                        $extension = $this->extensionFromMimeType($attachment['mimeType'] ?? null);
                        $filename = pathinfo($attachment['filename'] ?? 'bukti-whatsapp', PATHINFO_FILENAME);
                        $safeName = Str::slug($filename) ?: 'bukti-whatsapp';
                        $path = 'public-incident-evidence/'.date('Y/m').'/'.$safeName.'-'.Str::random(8).'.'.$extension;

                        Storage::disk('public')->put($path, $binary);

                        $attachment['path'] = $path;
                        $attachment['url'] = Storage::disk('public')->url($path);
                        $attachment['sizeBytes'] = strlen($binary);
                    }
                } else {
                    $attachment['downloadFailed'] = true;
                    $attachment['note'] = 'Data bukti dari bot bukan base64 valid.';
                }
            }

            unset($attachment['data']);

            return $attachment;
        })->values()->all();
    }

    private function payloadWithoutAttachmentData(array $payload): array
    {
        if (isset($payload['attachments']) && is_array($payload['attachments'])) {
            foreach ($payload['attachments'] as &$attachment) {
                if (is_array($attachment)) {
                    unset($attachment['data']);
                }
            }
        }

        return $payload;
    }

    private function extensionFromMimeType(?string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            default => 'bin',
        };
    }

    private function generateTicketNumber(): string
    {
        return 'WA-CSIRT-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
    }

    private function parseIncidentTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
