# Laporan Kegiatan Harian SIKANDI
**Bulan:** September 2026
**Jenis Kegiatan:** Tugas Pokok
**Total Hari Kerja:** 22 Hari (Senin - Jumat)

Berikut adalah rekapitulasi kegiatan harian untuk pelaporan E-Kinerja/Logbook harian. Setiap hari kerja dibagi menjadi 2 kegiatan utama dengan estimasi masing-masing 4 jam kerja (Total 8 Jam/hari), yang dipetakan langsung ke dalam 2 Rencana Aksi yang tersedia:
1. **[RA-1]** Menyediakan laporan Kerangka Kerja Pengelolaan Keamanan Informasi Pemerintah Daerah
2. **[RA-2]** Menyediakan laporan Pengelolaan Aset Informasi Pemerintah Daerah

---

| Tanggal | Rencana Aksi | Uraian Kegiatan | Output | Waktu (Jam) |
| :--- | :--- | :--- | :--- | :---: |
| **01/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Analisis arsitektur sistem SIKANDI dan perancangan skema database untuk modul keamanan dan kontrol akses (RBAC). | Dokumen Arsitektur & Skema DB | 4 |
| | **[RA-2]** Pengelolaan Aset | Pembuatan rancangan basis data Configuration Management Database (CMDB) untuk inventarisasi aset informasi daerah. | Skema Database Aset | 4 |
| **02/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Implementasi sistem Autentikasi dan Role-Based Access Control (Super Admin, Admin Persandian, dll) pada dashboard. | Modul Autentikasi & Role | 4 |
| | **[RA-2]** Pengelolaan Aset | Pengembangan modul CRUD (Create, Read, Update, Delete) untuk entitas Organisasi Perangkat Daerah (OPD) dan Departemen. | Modul OPD | 4 |
| **03/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Konfigurasi sistem logging dan Audit Trail (Sistem Audit Log) untuk merekam setiap perubahan krusial oleh user. | Sistem Audit Trail | 4 |
| | **[RA-2]** Pengelolaan Aset | Pembuatan modul manajemen Lokasi dan Ruang Server (Data Center) untuk pemetaan fisik aset informasi. | Modul Lokasi Aset | 4 |
| **04/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Setup environment dan inisialisasi repositori Git untuk SIKANDI Agent (berbasis Python) untuk deteksi ancaman. | Repositori & Environment | 4 |
| | **[RA-2]** Pengelolaan Aset | Implementasi relasi antar entitas (Configuration Items) pada CMDB untuk memetakan ketergantungan aset. | Logika Relasi CMDB | 4 |
| **07/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Pengembangan script instalasi otomatis (setup.sh) dan systemd service untuk SIKANDI Agent di server Linux. | Script Instalasi Agent | 4 |
| | **[RA-2]** Pengelolaan Aset | Pengembangan visualisasi Graph Topology interaktif untuk melihat hubungan antar aset (Hardware, Software, Jaringan). | Antarmuka Graph Aset | 4 |
| **08/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Pembuatan API endpoint pada backend Laravel untuk menerima metrik keamanan dan status heartbeat dari Agent. | API Gateway Agent | 4 |
| | **[RA-2]** Pengelolaan Aset | Pengembangan modul IT Asset Management (ITAM) untuk pendataan spesifikasi Hardware dan Server Pemda. | Modul ITAM Hardware | 4 |
| **09/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Penulisan modul Network Monitor pada Python Agent untuk melacak koneksi mencurigakan menggunakan psutil. | Modul Network Monitor | 4 |
| | **[RA-2]** Pengelolaan Aset | Pengembangan form input inventaris Software dan Lisensi pada modul ITAM. | Modul ITAM Software | 4 |
| **10/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Implementasi firewall rule parser (iptables/UFW) pada Agent untuk otomatisasi pemblokiran IP mencurigakan. | Modul Firewall Otomatis | 4 |
| | **[RA-2]** Pengelolaan Aset | Penyesuaian antarmuka Dashboard utama untuk menampilkan statistik jumlah aset terdaftar dan status server. | Widget Dashboard Aset | 4 |
| **11/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Pengembangan arsitektur Zero-Trust: verifikasi token otorisasi Agent ke Server Pusat. | Modul Zero-Trust Auth | 4 |
| | **[RA-2]** Pengelolaan Aset | Pembuatan halaman laporan Detail Aset (CI Detail) yang mencakup riwayat insiden pada server tertentu. | Halaman Profil Aset | 4 |
| **14/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Pembuatan struktur tabel dan logika backend untuk modul Security Operations Center (SOC) dan Incident Response. | Modul SOC & Insiden | 4 |
| | **[RA-2]** Pengelolaan Aset | Integrasi data aset website ke dalam sistem monitoring uptime dan pencatatan error log secara periodik. | Modul Monitoring Website | 4 |
| **15/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Pengembangan UI halaman persetujuan tindakan (Approval) SOC untuk analis keamanan (Human-in-the-Loop). | Antarmuka SOC Approval | 4 |
| | **[RA-2]** Pengelolaan Aset | Impor massal data aset website Pemda menggunakan database seeder untuk pengujian beban monitoring. | Data Dummy & Seeder | 4 |
| **16/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Pengaturan limitasi request (Rate Limiting) pada rute otentikasi login untuk mencegah serangan Brute Force. | Konfigurasi Rate Limit | 4 |
| | **[RA-2]** Pengelolaan Aset | Desain dan implementasi komponen navigasi (Sidebar) untuk mempermudah akses ke menu Manajemen IT dan Aset. | UI/UX Navigasi Aset | 4 |
| **17/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Integrasi reCAPTCHA v3 pada halaman login administrator untuk memblokir aktivitas bot. | Fitur reCAPTCHA | 4 |
| | **[RA-2]** Pengelolaan Aset | Pembuatan fitur pencarian global (Global Search) dengan shortcut Ctrl+K untuk menemukan aset dengan cepat. | Fitur Pencarian Global | 4 |
| **18/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Implementasi fitur Account Lockout (Kunci Akun Sementara) jika mendeteksi percobaan login gagal berulang kali. | Fitur Lockout Keamanan | 4 |
| | **[RA-2]** Pengelolaan Aset | Optimalisasi query relasi aset dan penerapan pagination pada tabel monitoring agar tidak membebani server. | Optimasi Query Database | 4 |
| **21/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Implementasi fitur Auto-Logout berbasis deteksi inaktivitas (idle timer) di browser menggunakan Javascript. | Fitur Auto-Logout | 4 |
| | **[RA-2]** Pengelolaan Aset | Pembaruan UI tabel daftar website (Pagination) serta penyusunan modul pemetaan IP Address aset. | UI Monitoring & IP | 4 |
| **22/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Pengembangan Two-Factor Authentication (2FA) menggunakan TOTP (Google Authenticator) beserta UI Setup Profil. | Fitur 2FA & QR Code | 4 |
| | **[RA-2]** Pengelolaan Aset | Whitelisting port aplikasi pihak ketiga (Ant Media Port 4444) pada aset server agar terhindar dari false-positive. | Aturan Whitelist Aset | 4 |
| **23/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Perbaikan logika auto-logout tab background menggunakan localStorage untuk mencegah anomali browser interval throttling. | Perbaikan Bug Keamanan | 4 |
| | **[RA-2]** Pengelolaan Aset | Pemetaan spesifikasi dan environment dua server uji coba (lukinode & atcs-PowerEdge-R740) ke dalam sistem inventaris CMDB. | Data Aset Uji Coba | 4 |
| **24/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Instalasi dan penyesuaian SIKANDI Agent pada dua server Linux yang berbeda untuk pengujian komparatif sistem deteksi. | SIKANDI Agent (Multi-Server) | 4 |
| | **[RA-2]** Pengelolaan Aset | Perekaman baseline utilisasi resource aset server (CPU/RAM/Network) sebelum intervensi keamanan diaktifkan (Fase "Before"). | Data Baseline Aset | 4 |
| **25/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Simulasi serangan jaringan internal dan pengujian sensitivitas deteksi (Suspicious Connection) pada kedua server uji coba. | Log Simulasi Serangan | 4 |
| | **[RA-2]** Pengelolaan Aset | Analisis "After" terkait sinkronisasi status (Online/Offline/Incident) pada dashboard pemantauan aset secara real-time. | Data Sinkronisasi Aset | 4 |
| **28/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Evaluasi komparatif (Before vs After) respon firewall otomatis dari SIKANDI Agent pada environment lukinode vs PowerEdge. | Laporan Komparasi Firewall | 4 |
| | **[RA-2]** Pengelolaan Aset | Investigasi anomali topologi jaringan server (Local IP vs Public IP) pasca aktivasi agen dan perbaikannya di CMDB. | Revisi Topologi Aset | 4 |
| **29/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Kustomisasi isolasi `config.yaml` SIKANDI Agent per-server untuk menyesuaikan parameter deteksi dengan fungsi asli server. | Konfigurasi Keamanan Khusus | 4 |
| | **[RA-2]** Pengelolaan Aset | Pengujian ketahanan (Stress Test) pengiriman log metrik secara simultan dari dua aset server ke webhook SIKANDI pusat. | Log Uji Ketahanan Aset | 4 |
| **30/09/2026** | **[RA-1]** Kerangka Kerja Keamanan | Penyusunan draf evaluasi eksperimen deteksi keamanan (Before-After) pada environment multi-server. | Draf Evaluasi Eksperimen | 4 |
| | **[RA-2]** Pengelolaan Aset | Sinkronisasi data metrik hasil uji coba ke dalam database CMDB sebagai acuan perbaikan arsitektur di bulan berikutnya. | Data Metrik Sinkronisasi | 4 |
