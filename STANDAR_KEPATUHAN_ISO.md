# Pemetaan Kepatuhan Standar Internasional (ISO)
## SIKANDI (Sistem Informasi Keamanan Informasi & Manajemen Aset Daerah)
**Dinas Komunikasi dan Informatika Kabupaten Ciamis**

Dokumen ini memuat daftar standar internasional (*International Organization for Standardization* / ISO) yang didukung dan diimplementasikan secara teknis pada platform **SIKANDI**. Pemetaan disusun berdasarkan bukti konkret arsitektur perangkat lunak, skema basis data, modul kontrol, serta alur kerja yang beroperasi pada sistem tanpa menyertakan kredensial atau informasi sensitif.

---

### 📑 Ringkasan Matriks Kepatuhan Standar ISO

| Standar ISO | Bidang / Domain Fokus | Modul & Fitur Terkait di SIKANDI | Tingkat Dukungan |
| :--- | :--- | :--- | :---: |
| **ISO/IEC 27001** | Sistem Manajemen Keamanan Informasi (SMKI / ISMS) | Risk Register, IKASANDI (Indeks KAMI), 2FA TOTP, Audit Trail, Agent Telemetry | **Penuh (Core)** |
| **ISO/IEC 20000-1** | Manajemen Layanan TI (ITSM / IT Service Management) | IT Service Desk, Penegakan SLA, CMDB & Relasi Konfigurasi | **Penuh** |
| **ISO 31000 / 27005** | Manajemen Risiko & Risiko Keamanan Informasi | Risk Register, Matriks Heatmap 5x5, Risk Treatment Mitigation | **Penuh** |
| **ISO/IEC 19770-1** | Manajemen Aset TI (IT Asset Management - ITAM) | ITAM Hardware/Software, QR Code Tracking, Siklus Hidup Aset | **Penuh** |
| **ISO/IEC 27035** | Manajemen Insiden Keamanan Informasi (CSIRT & SOC) | Alur 6 Tahap CSIRT, Forensik Digital, Automasi Respon, Inbound CSIRT | **Penuh** |
| **ISO 22301** | Kelangsungan Layanan & Pemulihan Bencana (BCMS) | Evaluasi BCP (Backup/Restore Drill), Pemantauan Uptime 24/7 | **Didukung** |
| **ISO 9001** | Tata Kelola Informasi Terdokumentasi (Klausul 7.5) | Knowledge Base, Manajemen Dokumen Kebijakan & SOP Berversi | **Didukung** |

---

### 1. ISO/IEC 27001 (Sistem Manajemen Keamanan Informasi / SMKI)
ISO/IEC 27001 merupakan pondasi arsitektur keamanan SIKANDI. Kepatuhan dipenuhi pada level klausul manajemen maupun kendali operasional (*Annex A*).

#### A. Klausul 6.1, 8.2 & 8.3 — Manajemen & Penilaian Risiko Keamanan Informasi
* **Implementasi:** SIKANDI menyediakan modul pendaftaran risiko yang terhubung langsung dengan inventaris aset informasi (*Configuration Item*).
* **Bukti Teknis:**
  * Model `app/Models/Risk.php` dan `app/Models/RiskTreatment.php`.
  * Kolom `compliance_framework` menyediakan opsi kepatuhan `"ISO 27001"` serta field `compliance_clause` untuk mencatat klausul terkait.
  * Form pembuatan dan mitigasi risiko pada `resources/views/security/create-risk.blade.php` dan `resources/views/security/edit-risk.blade.php`.
  * Kalkulasi tingkat risiko bawaan (*Inherent Risk Score = Likelihood x Impact*) dengan visualisasi Matriks Heatmap 5x5 pada `resources/views/security/risks.blade.php`.

#### B. Annex A.5.9 & A.8.1 — Inventarisasi & Klasifikasi Aset Informasi
* **Implementasi:** Seluruh aset perangkat keras, sistem operasi, aplikasi, dan database diinventarisasi secara terstruktur beserta penanggung jawab dan tingkat kritikalitasnya.
* **Bukti Teknis:**
  * Modul CMDB (`app/Models/ConfigurationItem.php`) dan ITAM (`app/Models/Asset.php`).
  * Klasifikasi nilai kritikalitas aset (`criticality`: *critical, high, medium, low*) untuk menentukan prioritas pengamanan.
  * Kategori kepemilikan dan relasi unit kerja (`organization_id`, `department_id`, penanggung jawab aset).

#### C. Annex A.5.15 s/d A.5.18 & A.8.5 — Kontrol Akses & Autentikasi Pengguna
* **Implementasi:** Pengamanan identitas pengguna berbasis pembatasan hak akses terkecil (*Principle of Least Privilege*).
* **Bukti Teknis:**
  * Two-Factor Authentication (2FA) berbasis algoritma TOTP (*Time-based One-Time Password*) pada `app/Http/Controllers/TwoFactorController.php`.
  * Pembatasan laju percobaan login (*Rate Limiting*) menggunakan middleware `throttle:5,1` pada rute web dan API (`routes/web.php` dan `routes/api.php`).
  * Mekanisme *Account Lockout* sementara setelah 5 kali kegagalan autentikasi beruntun pada `app/Http/Controllers/AuthController.php`.
  * Role-Based Access Control (RBAC) bertingkat (*Super Admin, Admin Persandian, IT Technician, Management, OPD User*) menggunakan *Spatie Permission*.
  * Pemutusan sesi otomatis berbasis deteksi inaktivitas (*Inactivity Auto-Logout*).

#### D. Annex A.8.15 & A.8.16 — Pencatatan Log & Pemantauan Aktivitas
* **Implementasi:** Rekam jejak audit (*Audit Trail*) yang tidak dapat diubah (*tamper-evident*) untuk seluruh transaksi data krusial.
* **Bukti Teknis:**
  * Perekaman log otomatis pada setiap mutasi model melalui trait `app/Traits/HasAuditLog.php` ke tabel `app/Models/AuditLog.php`.
  * Pengumpulan telemetri dan log peristiwa keamanan host secara terpusat melalui SIKANDI Agent pada `app/Models/SecurityEvent.php`.
  * Antarmuka pemantauan log audit administrator pada `resources/views/admin/audit-logs.blade.php`.

#### E. Annex A.8.24 — Penerapan Kriptografi & Pengamanan Sandi
* **Implementasi:** Verifikasi transmisi data terenkripsi dan pemanfaatan sertifikat elektronik resmi.
* **Bukti Teknis:**
  * Pengecekan otomatis masa aktif dan validitas sertifikat TLS/SSL pada portal web pemda melalui `app/Services/WebsiteMonitoringService.php`.
  * Kategori evaluasi mandiri `CRY-02` pada kuesioner IKASANDI untuk adopsi Tanda Tangan Elektronik (TTE) tersertifikasi BSrE BSSN.

#### F. Integrasi Kerangka Kerja Nasional (Indeks KAMI BSSN / IKASANDI)
* **Implementasi:** Penilaian mandiri indikator keamanan informasi untuk seluruh Organisasi Perangkat Daerah (OPD).
* **Bukti Teknis:**
  * Skema framework `database/seeders/IkasandiQuestionSeeder.php` yang mencakup 6 area kendali:
    1. Tata Kelola Keamanan Informasi & Kebijakan (`GOV`)
    2. Inventarisasi & Pengamanan Aset Informasi (`AST`)
    3. Penerapan Kriptografi & Pengamanan Sandi (`CRY`)
    4. Kelangsungan Layanan, Backup & Pemulihan Data (`BCP`)
    5. Manajemen Insiden Siber & Pelaporan CSIRT (`INC`)
    6. Peningkatan Kesadaran Keamanan Informasi SDM (`HRD`)

---

### 2. ISO/IEC 20000-1 (Sistem Manajemen Layanan TI / ITSM)
Standar internasional untuk tata kelola layanan teknologi informasi, menjamin transparansi penyelesaian keluhan dan pemeliharaan infrastruktur.

#### A. Klausul 8.2 & 8.3 — Portofolio Layanan & Manajemen Tingkat Layanan (SLA)
* **Implementasi:** Penyediaan katalog layanan TIK terpadu beserta target waktu respon dan penyelesaian (*Service Level Agreement*).
* **Bukti Teknis:**
  * Model `app/Models/Service.php` mendata kategori layanan dan durasi resolusi (`sla_resolution_hours`).
  * Model `app/Models/Ticket.php` menghitung batas waktu SLA secara otomatis (`sla_due_at`) saat tiket dibuat (`app/Http/Controllers/TicketController.php`).

#### B. Klausul 8.5.1 — Manajemen Konfigurasi (CMDB)
* **Implementasi:** Pemetaan inventaris komponen layanan beserta relasi ketergantungannya (*Dependency Mapping*).
* **Bukti Teknis:**
  * Model `app/Models/ConfigurationItem.php` dan `app/Models/CiRelationship.php`.
  * Relasi konfigurasi hierarkis: `runs_on`, `depends_on`, `connected_to`, `hosted_on`.
  * Visualisasi *Interactive Graph Topology* interaktif pada `app/Http/Controllers/CmdbController.php` (`cmdb.graph`).

#### C. Klausul 8.6 — Manajemen Insiden & Permintaan Layanan
* **Implementasi:** Pusat bantuan terpadu (*Service Desk*) bagi aparatur pemerintah daerah.
* **Bukti Teknis:**
  * Modul tiket pada `app/Http/Controllers/TicketController.php` yang mengklasifikasikan:
    * *Service Request* (Permintaan Layanan)
    * *Incident* (Gangguan Operasional)
    * *Access Request* (Permohonan Akun/Akses)
    * *Maintenance* (Pemeliharaan Berkala)
    * *Question* (Konsultasi Teknis)
  * Alur status tiket: `open` ➔ `assigned` ➔ `in_progress` ➔ `waiting` ➔ `resolved` ➔ `closed`.

---

### 3. ISO 31000 & ISO/IEC 27005 (Sistem Manajemen Risiko Organisasi & TI)
Panduan terstruktur untuk identifikasi, analisis, evaluasi, dan mitigasi risiko teknologi dan informasi.

* **Implementasi:** SIKANDI menerapkan siklus hidup manajemen risiko komprehensif mulai dari penaksiran bahaya hingga rencana tindakan perlakuan risiko.
* **Bukti Teknis:**
  * **Identifikasi Risiko:** Model `app/Models/Risk.php` mencatat skenario ancaman (*threat*), kerentanan (*vulnerability*), dan aset terdampak (*CI / Asset*).
  * **Analisis Kuantitatif & Kualitatif:**
    * Skala Kemungkinan (*Likelihood: 1 s/d 5*) dan Skala Dampak (*Impact: 1 s/d 5*).
    * Estimasi dampak kerugian finansial (`financial_impact_estimate`) dan estimasi waktu henti operasional dalam jam (`downtime_hours_estimate`).
  * **Perlakuan Risiko (*Risk Treatment*):**
    * Model `app/Models/RiskTreatment.php` menyediakan 4 strategi baku penanganan risiko:
      1. **Mitigate:** Menerapkan kontrol mitigasi tambahan.
      2. **Accept:** Menerima risiko dengan batas toleransi yang disetujui pimpinan.
      3. **Transfer:** Mengalihkan risiko (asuransi/pihak ketiga).
      4. **Avoid:** Menghentikan proses yang memicu timbulnya risiko.
    * Penetapan penanggung jawab mitigasi (*Assigned Owner*) dan target tanggal penyelesaian (*Due Date*).

---

### 4. ISO/IEC 19770-1 (Manajemen Aset TI / ITAM)
Standar pengelolaan siklus hidup aset teknologi informasi (perangkat keras, perangkat lunak, dan fasilitas data center).

* **Implementasi:** Pengawasan siklus hidup aset mulai dari pengadaan hingga pemusnahan/penghapusan.
* **Bukti Teknis:**
  * **Spesifikasi Aset:** Model `app/Models/Asset.php` dan `database/seeders/AssetCategorySeeder.php` mencatat detail perangkat keras (CPU, RAM, Penyimpanan, Nomor Seri, MAC Address, IP Address, Lisensi).
  * **Pelabelan Fisik Digital (QR Code Tagging):**
    * Pembuatan label inventaris ber-QR Code pada `app/Http/Controllers/AssetController.php` (`printLabel`).
    * Endpoint pemindaian cepat mobile via token pada rute `/itam/scan/{token}`.
  * **Manajemen Siklus Hidup (*Lifecycle Status*):**
    * Siklus aset terdata: `procurement` (pengadaan), `in_use` (aktif), `maintenance` (perawatan), `retired` (purna tugas), `disposed` (dihapus/lelang).
    * Riwayat perbaikan dan log pemeliharaan fisik pada model `app/Models/AssetMaintenance.php`.

---

### 5. ISO/IEC 27035-1 & 27035-2 (Manajemen Insiden Keamanan Informasi / CSIRT & SOC)
Standar pedoman kesiapsiagaan, deteksi, pelaporan, dan penanggulangan insiden keamanan siber.

* **Implementasi:** Penanganan insiden siber terstruktur oleh Tim Tanggap Insiden Siber (CSIRT) Pemerintah Kabupaten Ciamis.
* **Bukti Teknis:**
  * **Tahapan Siklus Insiden Berstandar:**
    * Kolom `workflow_status` pada `app/Models/SecurityIncident.php` membagi penanganan ke dalam 6 fase ISO 27035:
      1. *Preparation* (Kesiapsiagaan aturan deteksi pada `app/Models/SecurityRule.php`).
      2. *Triage / Identification* (Klasifikasi insiden dan penentuan tingkat keparahan / *severity*).
      3. *Containment* (Tindakan pembendungan: isolasi server, pemblokiran IP pada `app/Services/ResponseExecutorService.php`).
      4. *Eradication* (Pembersihan akar serangan dan penugasan tim pada `app/Models/SecurityIncidentTask.php`).
      5. *Recovery* (Pemulihan layanan dan verifikasi fungsi normal).
      6. *Post-Incident Review* (Evaluasi pasca insiden: pencatatan *root_cause* dan *resolution_summary*).
  * **Rantai Bukti Digital (*Chain of Custody*):**
    * Model `app/Models/SecurityIncidentEvidence.php` mencatat bukti forensik digital, ukuran file, deskripsi, dan identitas pengunggah.
  * **Integrasi Inbound Pelaporan Publik:**
    * Penerimaan laporan insiden publik dari bot WhatsApp CSIRT melalui `app/Models/PublicIncidentReport.php` dengan alur verifikasi analis persandian sebelum dieskalasi ke insiden resmi.

---

### 6. ISO 22301 (Sistem Manajemen Kelangsungan Layanan / BCMS)
Standar untuk merencanakan, menetapkan, dan mengoperasikan sistem penjaminan kelangsungan layanan saat terjadi gangguan darurat atau bencana.

* **Implementasi:** Menjamin layanan publik tetap dapat diakses dan kesiapan cadangan data jika terjadi bencana sistemik.
* **Bukti Teknis:**
  * **Pencadangan & Uji Pemulihan (*Disaster Recovery*):**
    * Kategori evaluasi IKASANDI `BCP` (`database/seeders/IkasandiQuestionSeeder.php`):
      * `BCP-01`: Jadwal rutin pencadangan basis data dan sistem.
      * `BCP-02`: Penyimpanan cadangan di lokasi terpisah (*off-site cloud storage*).
      * `BCP-03`: Kewajiban uji coba pemulihan data (*restore drill*) berkala.
  * **Pemantauan Ketersediaan Layanan 24/7:**
    * Pengecekan otomatis ketersediaan website Pemda Ciamis melalui cron scheduler `app/Console/Commands/CheckWebsitesCommand.php`.
    * Otomatisasi pembentukan tiket insiden prioritas tinggi saat layanan publik terdeteksi tidak dapat diakses (`WebsiteMonitoringService.php`).

---

### 7. ISO 9001 (Sistem Manajemen Mutu — Klausul 7.5 Informasi Terdokumentasi)
Standar tata kelola dokumen formal, keteraturan prosedur operasional standar (SOP), dan pengendalian versi dokumen.

* **Implementasi:** Pengarsipan regulasi, SOP persandian, dan dokumentasi teknis secara terpusat dan berversi.
* **Bukti Teknis:**
  * Model `app/Models/Document.php` dan `app/Models/KnowledgeArticle.php`.
  * Penomoran kode unik dokumen otomatis (`DOC-xxxx`), pencatatan versi (`version`), label klasifikasi kerahasiaan (`is_confidential`), serta relasi dokumen ke unit kerja dan aset terkait.

---

### 🏛️ Keselarasan dengan Kerangka Kerja Regulasi Nasional
Penerapan standar ISO di atas di SIKANDI selaras dan mendukung kepatuhan terhadap regulasi nasional:
1. **Peraturan BSSN Nomor 8 Tahun 2020** tentang Sistem Pengamanan dalam Penyelenggaraan Sistem Elektronik (SPSE berbasis ISO 27001).
2. **Peraturan Presiden Nomor 95 Tahun 2018** tentang Sistem Pemerintahan Berbasis Elektronik (SPBE) — Domain Manajemen Keamanan dan Layanan TI.
3. **Instrumen Indeks Keamanan Informasi (Indeks KAMI BSSN)** — Diintegrasikan secara *native* pada modul IKASANDI.

---
*Dokumen ini diperbarui secara berkala sesuai perkembangan arsitektur dan kapabilitas platform SIKANDI.*
