# SIKANDI-Agent: Pedoman Instalasi & Konfigurasi

SIKANDI-Agent adalah *service* berbasis Python ringan yang dipasang pada server/VM target. Agen ini berfungsi melakukan monitoring (CPU/RAM/Disk), mendeteksi anomali keamanan (File Integrity, Brute Force), hingga mengeksekusi tindakan isolasi jaringan (blokir IP via `iptables`) berdasarkan perintah dari Dashboard SIKANDI.

---

## 🛠️ Prasyarat Sistem (Prerequisites)

Sebelum melakukan instalasi pada server target, pastikan server memenuhi spesifikasi berikut:
1. **OS Linux** (Ubuntu / Debian / CentOS / RHEL)
2. **Python 3.8** atau lebih baru, beserta `pip` (`python3-pip`).
3. **Akses Root / Sudo:** Mutlak dibutuhkan karena agen akan membaca log sistem (`/var/log/auth.log`) dan mengeksekusi blokir via `iptables`.
4. (Opsional) **Node.js & PM2** untuk menjalankan agen di latar belakang *(background service)*.

---

## 🚀 Langkah Instalasi

### 1. Salin File Agent
Pindahkan folder `sikandi-agent` dari repositori utama ke server target (misal diletakkan di `/opt/sikandi-agent/`).
```bash
sudo cp -r sikandi-agent /opt/
cd /opt/sikandi-agent
```

### 2. Install Dependencies
Pastikan berada di dalam folder agen, lalu jalankan instalasi *library* Python:
```bash
sudo apt update && sudo apt install python3-pip -y
sudo pip3 install -r requirements.txt
```

### 3. Konfigurasi Awal (config.yaml)
Buka file `config.yaml` menggunakan editor teks (nano/vim):
```bash
nano config.yaml
```
Sesuaikan `url` dengan alamat server utama SIKANDI Anda:
```yaml
api:
  url: "https://sikandi.ciamiskab.go.id/api/v1/agent"
  token: "MASUKKAN_TOKEN_ANDA_DISINI" 
```
*Catatan: Biarkan token bertuliskan `MASUKKAN_TOKEN_ANDA_DISINI`. Agen akan menanyakannya saat pertama kali dijalankan.*

---

## 🔐 Proses Registrasi Agent

SIKANDI mengamankan setiap agen menggunakan sistem *Zero Trust Registration*.
Jalankan perintah berikut untuk memulai registrasi:
```bash
sudo python3 agent.py
```

**Alur Registrasi:**
1. Di layar, agen akan mendeteksi token kosong dan meminta **Registration Token**. 
   *(Dapatkan Token Registrasi Global ini dari Menu Settings > General di Dashboard SIKANDI).*
2. Setelah di-Enter, agen akan mengirim data *Hostname* ke SIKANDI.
3. **PENTING:** Buka Dashboard Web SIKANDI -> Menu **Server Agents**. Anda akan melihat agen baru berstatus "Pending". Klik tombol **Approve**.
4. SIKANDI akan memunculkan popup berisi **Token Sanctum Rahasia**.
5. *Copy* token tersebut, dan *Paste* kembali ke terminal server target.
6. Selesai! `config.yaml` akan otomatis diperbarui dan agen mulai memonitor server. Tekan `Ctrl+C` untuk mematikan sementara.

---

## 🏃‍♂️ Menjalankan di Latar Belakang (Production Mode)

Agar agen tetap hidup saat terminal ditutup, gunakan **PM2**.
Karena agen butuh akses *root* untuk memodifikasi `iptables`, pastikan PM2 dijalankan oleh *root*.

```bash
# Install PM2 jika belum ada (membutuhkan Node.js)
sudo npm install -g pm2

# Jalankan Agent menggunakan interpreter Python3
sudo pm2 start agent.py --name "sikandi-agent" --interpreter python3

# Simpan state PM2 agar auto-start saat server restart
sudo pm2 save
sudo pm2 startup
```

---

## 🛡️ Fitur Otomatisasi (Blacklist Sync & Iptables)

Agen SIKANDI memiliki *thread* **Blacklist Sync** yang berjalan setiap 30 detik.
1. Agen akan mengunduh daftar IP yang di-blokir *(HitL Execution)* dari SIKANDI Utama.
2. Jika ada IP penyerang baru, agen akan otomatis mengeksekusi:
   `iptables -A INPUT -s {IP} -j DROP`
3. Seluruh log eksekusi blokir dapat dilihat menggunakan perintah:
   ```bash
   sudo pm2 logs sikandi-agent
   ```

---

## 🧪 Testing Mode
Jika Anda ingin mengetes alur insiden keamanan dari agen ke Dashboard *tanpa* melakukan serangan sungguhan, jalankan menggunakan argumen test:
```bash
sudo python3 agent.py --test-security
```
Agen akan mengirimkan serangan *Brute Force Mockup* ke dashboard setiap 10 detik.
