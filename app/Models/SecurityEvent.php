<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $fillable = [
        'event_id', 'agent_id', 'timestamp', 'event_type', 'action',
        'hostname', 'username', 'source_ip', 'process', 'severity',
        'risk_score', 'reason', 'metadata', 'incident_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'timestamp' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function incident()
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }

    public function getNarrativeAttribute()
    {
        $type = strtoupper($this->event_type);
        $action = strtoupper($this->action);
        $ip = $this->source_ip ?? 'Unknown IP';
        $host = $this->hostname ?? 'Unknown Server';
        $user = $this->username ?? 'Unknown User';

        if ($type === 'FAIL2BAN_BAN' || ($type === 'FAIL2BAN' && $action === 'BAN')) {
            return "Sistem keamanan Fail2Ban pada server **{$host}** secara otomatis **telah memblokir IP {$ip}**. IP ini terdeteksi melakukan aktivitas mencurigakan secara berulang pada layanan (jail) SSH dan dimasukkan ke dalam daftar hitam firewall untuk mencegah serangan lebih lanjut.";
        }

        if ($type === 'FAIL2BAN_UNBAN' || ($type === 'FAIL2BAN' && $action === 'UNBAN')) {
            return "Masa pemblokiran firewall untuk IP **{$ip}** pada server **{$host}** telah berakhir. Sistem secara otomatis telah **membuka kembali akses (Unban)** untuk IP tersebut. Jika IP ini kembali melakukan pelanggaran, sistem akan memblokirnya kembali.";
        }

        if ($type === 'LOGIN' && $action === 'SUCCESS') {
            return "Pengguna **{$user}** telah berhasil login ke server **{$host}** dari alamat IP **{$ip}**. Otentikasi berjalan normal tanpa ada indikasi anomali.";
        }

        if ($type === 'LOGIN' && $action === 'FAILED') {
            return "Terdeteksi kegagalan login dari pengguna **{$user}** di server **{$host}** (IP: **{$ip}**). Percobaan ini ditolak oleh sistem karena kredensial tidak valid atau tidak memiliki kunci akses yang sesuai.";
        }

        // Fallback for other events
        return "Sistem mendeteksi aktivitas **{$type}** ({$action}) pada server **{$host}** dari IP **{$ip}**. Alasan yang tercatat: ".($this->reason ?? 'Aktivitas anomali terdeteksi oleh SIKANDI Agent.');
    }
}
