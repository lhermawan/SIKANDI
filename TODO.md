# IKASANDI Enhancement Tasks

- [x] 1. Dynamic Penalty Scoring (Validasi Skor Berbasis Fakta)
  - [x] Update `Assessment::recalculateScores()` di `app/Models/Assessment.php` untuk memasukkan penalti dari Security Incidents dan Website Monitoring.
  - [x] Tambahkan field `penalty_score` atau sesuaikan `compliance_score` dan tampilkan logikanya di UI.
- [x] 2. Workflow Verifikasi Admin Persandian (Approval System)
  - [x] Tambahkan tombol Approve/Reject dan input catatan verifikasi untuk role Admin Persandian di `IkasandiController`.
- [x] 3. Leaderboard Kepatuhan OPD (Gamifikasi)
  - [x] Buat UI Leaderboard (Top 5 & Bottom 5 OPD) di `resources/views/ikasandi/dashboard.blade.php`.
- [x] 4. Ekspor Sertifikat / Laporan (Cetak Laporan)
  - [x] Tambahkan route dan fungsi `printAssessment` di Controller.
  - [x] Buat layout `print.blade.php` dengan CSS `@media print` untuk mencetak laporan.
 
  #   W e b s i t e   &   S S L   H e a l t h   M o n i t o r i n g   T a s k s  
 -   [ x ]   1 .   A d d   E d i t   &   D e l e t e   F u n c t i o n a l i t y   f o r   W e b s i t e s  
     -   [ x ]   T a m b a h k a n   r o u t e   P U T / P A T C H   u n t u k   \ u p d a t e \   d a n   D E L E T E   u n t u k   \ d e s t r o y \   d i   \  o u t e s / w e b . p h p \ .  
     -   [ x ]   I m p l e m e n t a s i k a n   m e t o d e   \ u p d a t e \   d a n   \ d e s t r o y \   d i   \ M o n i t o r i n g C o n t r o l l e r . p h p \ .  
     -   [ x ]   T a m b a h k a n   t o m b o l   E d i t   ( M o d a l )   d a n   H a p u s   ( F o r m   D e l e t e )   d i   U I   \ w e b s i t e s . b l a d e . p h p \   d e n g a n   p r o t e k s i   R o l e .  
 -   [ x ]   2 .   A d d   S e a r c h   a n d   F i l t e r s  
     -   [ x ]   M o d i f i k a s i   \ w e b s i t e s \   m e t h o d   d i   \ M o n i t o r i n g C o n t r o l l e r \   u n t u k   m e n e r i m a   p a r a m e t e r   p e n c a r i a n   ( n a m a / u r l )   d a n   f i l t e r   ( s t a t u s   u p / d o w n / s s l   w a r n i n g ,   f i l t e r   b y   O P D ) .  
     -   [ x ]   T a m b a h k a n   f o r m   f i l t e r   b a r   d i   U I .  
 -   [ x ]   3 .   I m p o r t   &   E x p o r t   D a t a   ( E x c e l / C S V )  
     -   [ x ]   S i a p k a n   f i l e   t e m p l a t e   E x c e l   u n t u k   d i - d o w n l o a d   p e n g g u n a .  
     -   [ x ]   B u a t   f i t u r   I m p o r t   E x c e l   u n t u k   m e n d a f t a r k a n   b a n y a k   w e b s i t e   s e k a l i g u s   k e   s i s t e m   m o n i t o r i n g   ( m a p p i n g   k e   O P D   d a n   C I ) .  
     -   [ x ]   B u a t   f i t u r   E x p o r t   E x c e l   u n t u k   m e n g u n d u h   l a p o r a n   s t a t u s   w e b s i t e   d a n   m a s a   a k t i f   S S L .  
     -   [ x ]   P a s t i k a n   f i l e   t e m p l a t e   E x c e l   b e r i s i   c o n t o h   d a t a   a s l i   ( d u m m y )   y a n g   d i a m b i l   d a r i   d a t a b a s e   a g a r   p e n g g u n a   p a h a m   f o r m a t   i s i a n n y a   ( m i s a l :   I D   O P D ,   I D   C I ,   f o r m a t   U R L ) .

### Bulk & Automated Website Checking
- [x] Buat Laravel Console Command (`php artisan sikandi:check-websites`) untuk melakukan pengecekan seluruh website.
- [x] Buat Laravel Queued Job (proses *chunking*) agar pengecekan 300+ website bisa berjalan di background tanpa membuat server PHP timeout/lemot.
- [x] Daftarkan Command tersebut ke dalam Laravel Scheduler agar pengecekan otomatis berjalan berkala (misal setiap jam/hari).
- [x] (Opsional) Tambahkan tombol "Check All" di halaman UI untuk men-trigger Queued Job secara manual.

### Antivirus-style Scanning UI (Real-time Progress)
- [ ] Ubah Queued Job menjadi **Laravel Job Batches** agar progress-nya bisa dilacak (Berapa % selesai, total diproses, dsb).
- [ ] Buat API Endpoint (contoh: `/monitoring/websites/batch-status/{id}`) untuk memberikan data progress scanning ke *frontend*.
- [ ] Buat Modal/UI Progress Bar ala "Antivirus" di `websites.blade.php`.
- [ ] Gunakan JavaScript (AJAX Polling / setInterval) untuk menembak API dan menganimasikan Progress Bar secara *real-time* saat tombol "Check All" diklik.