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
        
        // Bersihkan N/A
        $ip = ($this->source_ip === 'N/A' || empty($this->source_ip)) ? null : $this->source_ip;
        $host = $this->hostname ?? 'Unknown Server';
        $user = ($this->username === 'N/A' || empty($this->username)) ? null : $this->username;
        
        $ipDisplay = $ip ?? 'Unknown IP';
        $userDisplay = $user ?? 'Sistem/Unknown User';

        if ($type === 'FAIL2BAN_BAN' || ($type === 'FAIL2BAN' && $action === 'BAN')) {
            return "Sistem keamanan Fail2Ban pada server **{$host}** secara otomatis **telah memblokir IP {$ipDisplay}**. IP ini terdeteksi melakukan aktivitas mencurigakan secara berulang pada layanan (jail) SSH dan dimasukkan ke dalam daftar hitam firewall untuk mencegah serangan lebih lanjut.";
        }

        if ($type === 'FAIL2BAN_UNBAN' || ($type === 'FAIL2BAN' && $action === 'UNBAN')) {
            return "Masa pemblokiran firewall untuk IP **{$ipDisplay}** pada server **{$host}** telah berakhir. Sistem secara otomatis telah **membuka kembali akses (Unban)** untuk IP tersebut. Jika IP ini kembali melakukan pelanggaran, sistem akan memblokirnya kembali.";
        }

        if ($type === 'LOGIN' && $action === 'SUCCESS') {
            return "Pengguna **{$userDisplay}** telah berhasil login ke server **{$host}** dari alamat IP **{$ipDisplay}**. Otentikasi berjalan normal tanpa ada indikasi anomali.";
        }

        if ($type === 'LOGIN' && $action === 'FAILED') {
            return "Terdeteksi kegagalan login dari pengguna **{$userDisplay}** di server **{$host}** (IP: **{$ipDisplay}**). Percobaan ini ditolak oleh sistem karena kredensial tidak valid atau tidak memiliki kunci akses yang sesuai.";
        }

        // Fallback for other events
        $sourceDesc = $ip ? " dari IP **{$ip}**" : " (Aktivitas Internal/Lokal)";
        $userDesc = $user ? " oleh user **{$user}**" : "";

        return "Sistem mendeteksi aktivitas **{$type}** ({$action}) pada server **{$host}**{$sourceDesc}{$userDesc}. Alasan yang tercatat: ".($this->reason ?? 'Aktivitas anomali terdeteksi oleh SIKANDI Agent.');
    }
}
