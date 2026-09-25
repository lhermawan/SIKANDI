@extends('layouts.app')

@section('title', 'Tinjau Laporan Publik: ' . $report->ticket_number)

@section('content')
<div class="space-y-6">
    {{-- Top Navigation & Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('security.public-incidents.index') }}" class="hover:text-blue-400 transition">Meja Triage Insiden Publik</a>
                <span>/</span>
                <span class="text-white">{{ $report->ticket_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight font-mono">{{ $report->ticket_number }}</h1>
                @if($report->status === 'pending_review')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Menunggu Review
                    </span>
                @elseif($report->status === 'verified')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        ✓ Terverifikasi Valid
                    </span>
                @elseif($report->status === 'rejected')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                        ✕ Ditolak (Tidak Valid)
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-400 mt-1">Diterima via channel <strong class="text-emerald-400 uppercase font-mono">{{ $report->source }}</strong> pada {{ $report->created_at->format('d F Y, H:i:s') }} WIB</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('security.public-incidents.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition border border-slate-700 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>{{ session('info') }}</div>
        </div>
    @endif

    {{-- Main Grid: Detail Laporan (Kiri) vs Action Triage (Kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        {{-- Kolom Kiri: Rincian Laporan Publik --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Data Pelapor & Kejadian --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
                <h3 class="text-base font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Informasi Pelapor & Identitas Insiden</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-950/60 p-3.5 rounded-xl border border-slate-800/80">
                        <span class="text-xs font-medium text-slate-400 block mb-1">Nama Pelapor / Instansi</span>
                        <span class="text-sm font-semibold text-white">{{ $report->reporter_name ?: 'Anonim' }}</span>
                    </div>

                    <div class="bg-slate-950/60 p-3.5 rounded-xl border border-slate-800/80">
                        <span class="text-xs font-medium text-slate-400 block mb-1">Nomor WhatsApp Pelapor</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-mono font-semibold text-emerald-400">{{ $report->whatsapp_from ?: '-' }}</span>
                            @if($report->whatsapp_from)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $report->whatsapp_from);
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer" class="text-xs text-slate-400 hover:text-emerald-400 flex items-center gap-1 transition" title="Buka Chat WhatsApp">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Chat
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-950/60 p-3.5 rounded-xl border border-slate-800/80">
                        <span class="text-xs font-medium text-slate-400 block mb-1">Dugaan Jenis Insiden</span>
                        <span class="inline-block px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 capitalize">
                            {{ $report->incident_type ?: 'Belum terklasifikasi' }}
                        </span>
                    </div>

                    <div class="bg-slate-950/60 p-3.5 rounded-xl border border-slate-800/80">
                        <span class="text-xs font-medium text-slate-400 block mb-1">Waktu Kejadian (Klaim Pelapor)</span>
                        <span class="text-sm text-slate-200">
                            {{ $report->incident_time ? $report->incident_time->format('d M Y, H:i') . ' WIB' : 'Tidak disebutkan spesifik' }}
                        </span>
                    </div>
                </div>

                {{-- Target Aset Terdampak --}}
                <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Aset / Sistem / Domain / IP yang Terdampak</span>
                    <div class="font-mono text-sm text-red-400 bg-slate-900 px-3 py-2 rounded-lg border border-slate-800 break-all">
                        {{ $report->affected_asset ?: 'Tidak disertakan' }}
                    </div>
                </div>

                {{-- Kronologi --}}
                <div class="space-y-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kronologi Kejadian:</span>
                    <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $report->chronology ?: 'Tidak ada kronologi yang diisikan.' }}
                    </div>
                </div>

                {{-- Dampak --}}
                <div class="space-y-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dampak yang Terjadi:</span>
                    <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $report->impact ?: 'Tidak ada keterangan dampak.' }}
                    </div>
                </div>
            </div>

            {{-- Bukti Attachment / Galeri --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <h3 class="text-base font-bold text-white flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Bukti Pendukung & Lampiran</span>
                    </span>
                    @if($report->evidence_note)
                        <span class="text-xs text-slate-400 italic font-normal">"{{ $report->evidence_note }}"</span>
                    @endif
                </h3>

                @php
                    $attachments = is_array($report->attachments) ? $report->attachments : [];
                @endphp

                @if(count($attachments) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($attachments as $att)
                            <div class="bg-slate-950 border border-slate-800 rounded-xl p-3.5 flex flex-col justify-between space-y-3">
                                <div class="flex items-start gap-3">
                                    @php
                                        $mime = $att['mimeType'] ?? '';
                                        $isImage = str_starts_with($mime, 'image/');
                                    @endphp
                                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 shrink-0">
                                        @if($isImage)
                                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @else
                                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-white truncate" title="{{ $att['filename'] ?? 'file' }}">
                                            {{ $att['filename'] ?? 'Bukti WhatsApp' }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            {{ isset($att['sizeBytes']) ? round($att['sizeBytes'] / 1024, 1) . ' KB' : 'Size N/A' }} • {{ $att['mimeType'] ?? 'Unknown MIME' }}
                                        </p>
                                    </div>
                                </div>

                                @if($isImage && !empty($att['url']))
                                    <div class="rounded-lg overflow-hidden border border-slate-800 bg-slate-900 max-h-48 flex items-center justify-center">
                                        <img src="{{ $att['url'] }}" alt="{{ $att['filename'] ?? 'Bukti' }}" class="object-cover w-full max-h-48 hover:scale-105 transition duration-300">
                                    </div>
                                @endif

                                @if(!empty($att['url']))
                                    <div class="pt-2 border-t border-slate-900 flex justify-end">
                                        <a href="{{ $att['url'] }}" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-blue-400 hover:text-blue-300 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            <span>Buka / Unduh File</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-slate-500 text-xs italic bg-slate-950/40 rounded-xl border border-slate-800/60">
                        Tidak ada file screenshot atau dokumen bukti yang dilampirkan oleh pelapor.
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Meja Triage & Aksi Validasi --}}
        <div class="space-y-6">
            @if($report->status === 'pending_review')
                {{-- Form Validasi & Eskalasi ke Security Incident --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="flex items-center gap-2 text-emerald-400 border-b border-slate-800 pb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <h3 class="text-base font-bold text-white">Validasi & Jadikan Insiden</h3>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Jika laporan ini <strong>valid dan terbukti ada indikasi serangan siber</strong>, klik tombol di bawah untuk membuat tiket <em>Security Incident</em> resmi dan memicu alur investigasi SOC SIKANDI.
                    </p>

                    <form method="POST" action="{{ route('security.public-incidents.escalate', $report->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Judul Insiden Siber <span class="text-red-400">*</span></label>
                            <input type="text" name="title" required value="{{ old('title', 'Laporan Publik: ' . ($report->incident_type ? ucfirst($report->incident_type) : 'Insiden') . ' pada ' . ($report->affected_asset ?: 'Sistem')) }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Klasifikasi Jenis <span class="text-red-400">*</span></label>
                                <select name="incident_type" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                    <option value="defacement" {{ old('incident_type', $report->incident_type) === 'defacement' ? 'selected' : '' }}>Web Defacement</option>
                                    <option value="phishing" {{ old('incident_type', $report->incident_type) === 'phishing' ? 'selected' : '' }}>Phishing</option>
                                    <option value="malware" {{ old('incident_type', $report->incident_type) === 'malware' ? 'selected' : '' }}>Malware</option>
                                    <option value="account_compromise" {{ old('incident_type', $report->incident_type) === 'account_compromise' ? 'selected' : '' }}>Akun Diretas</option>
                                    <option value="data_exposure" {{ old('incident_type', $report->incident_type) === 'data_exposure' ? 'selected' : '' }}>Kebocoran Data</option>
                                    <option value="vulnerability" {{ old('incident_type', $report->incident_type) === 'vulnerability' ? 'selected' : '' }}>Kerentanan Sistem</option>
                                    <option value="website_attack" {{ old('incident_type', $report->incident_type) === 'website_attack' ? 'selected' : '' }}>Serangan Website</option>
                                    <option value="network_attack" {{ old('incident_type', $report->incident_type) === 'network_attack' ? 'selected' : '' }}>Serangan Jaringan</option>
                                    <option value="other" {{ old('incident_type', $report->incident_type) === 'other' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Tingkat Severity <span class="text-red-400">*</span></label>
                                <select name="severity" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Kaitkan ke OPD / Pemilik Sistem <span class="text-red-400">*</span></label>
                            <select name="organization_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                <option value="">-- Pilih OPD Penanggungjawab --</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Kaitkan ke Aset CMDB (Opsional)</label>
                            <select name="ci_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                <option value="">-- Tidak dikaitkan spesifik --</option>
                                @foreach($cis as $ci)
                                    <option value="{{ $ci->id }}" {{ old('ci_id') == $ci->id ? 'selected' : '' }}>{{ $ci->name }} ({{ $ci->type }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Tindak Lanjut Verifikator</label>
                            <textarea name="review_notes" rows="2" placeholder="Catatan internal analis saat validasi..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">{{ old('review_notes', 'Laporan valid. Diangkat menjadi penanganan insiden keamanan siber resmi.') }}</textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                            <span>🚀 Validasi & Buat Security Incident</span>
                        </button>
                    </form>
                </div>

                {{-- Form Tolak Laporan (Spam / Hoaks / Tidak Valid) --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center gap-2 text-rose-400 border-b border-slate-800 pb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <h3 class="text-base font-bold text-white">Tolak Laporan</h3>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Jika laporan merupakan pesan spam, hoaks, data palsu, atau bukan masalah keamanan siber, Anda dapat menolak laporan ini.
                    </p>

                    <form method="POST" action="{{ route('security.public-incidents.reject', $report->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Alasan Penolakan <span class="text-red-400">*</span></label>
                            <textarea name="review_notes" rows="3" required placeholder="Contoh: Informasi tidak lengkap, domain bukan milik Pemkab Ciamis, atau pesan spam/duplikat..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">{{ old('review_notes') }}</textarea>
                        </div>

                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menolak laporan publik ini?')" class="w-full py-2.5 px-4 bg-rose-600/20 hover:bg-rose-600/30 text-rose-400 border border-rose-500/30 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2">
                            <span>✕ Tandai Laporan Ditolak</span>
                        </button>
                    </form>
                </div>
            @else
                {{-- Status Hasil Triage Sebelumnya --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Hasil Triage Analis</span>
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-slate-400 block mb-0.5">Status Akhir:</span>
                            @if($report->status === 'verified')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 inline-block">
                                    ✓ Terverifikasi Valid
                                </span>
                            @elseif($report->status === 'rejected')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 inline-block">
                                    ✕ Laporan Ditolak
                                </span>
                            @endif
                        </div>

                        <div>
                            <span class="text-xs text-slate-400 block mb-0.5">Ditinjau Oleh:</span>
                            <span class="text-sm font-semibold text-white">{{ $report->reviewer?->name ?: 'Administrator' }}</span>
                            <span class="text-xs text-slate-400 block">{{ $report->reviewed_at?->format('d M Y, H:i') }} WIB</span>
                        </div>

                        <div>
                            <span class="text-xs text-slate-400 block mb-0.5">Catatan Verifikator:</span>
                            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 text-xs text-slate-300">
                                {{ $report->review_notes ?: 'Tidak ada catatan.' }}
                            </div>
                        </div>

                        @if($report->securityIncident)
                            <div class="pt-3 border-t border-slate-800">
                                <span class="text-xs text-slate-400 block mb-1.5">Insiden Siber Terkait:</span>
                                <a href="{{ route('security.incidents.show', $report->security_incident_id) }}" class="p-3.5 bg-blue-500/10 border border-blue-500/30 rounded-xl block hover:bg-blue-500/20 transition group">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono font-bold text-sm text-blue-400 group-hover:underline">{{ $report->securityIncident->incident_code }}</span>
                                        <span class="text-xs text-slate-400 uppercase font-semibold">{{ $report->securityIncident->workflow_status }}</span>
                                    </div>
                                    <p class="text-xs text-slate-300 mt-1 truncate">{{ $report->securityIncident->title }}</p>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
