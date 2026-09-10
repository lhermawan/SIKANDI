import requests
import time
import socket
import random
import sys

SIKANDI_URL = "http://localhost:8000/api/v1/agent"

# GANTI INI DENGAN TOKEN DARI HALAMAN SERVER AGENTS
REGISTRATION_TOKEN = "UQESLKrMzhqKXyKrNkjrd75ZWivMf0cw"

def main():
    if REGISTRATION_TOKEN == "MASUKKAN_TOKEN_ANDA_DISINI":
        print("ERROR: Harap isi REGISTRATION_TOKEN di script terlebih dahulu!")
        sys.exit(1)

    print("=== SIKANDI Agent Simulator ===")
    print("[1] Registrasi Agent...")
    
    # 1. Registrasi
    hostname = socket.gethostname()
    try:
        reg_response = requests.post(f"{SIKANDI_URL}/register", json={
            "registration_token": REGISTRATION_TOKEN,
            "hostname": hostname,
            "os": "Windows/Linux",
            "os_version": "Simulation",
            "agent_version": "1.0.0"
        })
        reg_data = reg_response.json()
    except Exception as e:
        print(f"Gagal koneksi ke SIKANDI: {e}")
        sys.exit(1)

    if reg_response.status_code != 201:
        print(f"Registrasi gagal: {reg_data.get('message')}")
        sys.exit(1)

    agent_id = reg_data['agent_id']
    print(f"Registrasi Berhasil! Agent ID: {agent_id}")
    print("\n>>> PENTING: Buka browser SIKANDI, masuk ke Server Agents.")
    print(">>> Klik tombol 'Approve' lalu COPY token Sanctum yang muncul.")
    
    agent_token = input("\nMasukkan Agent Token (Sanctum) yang baru digenerate: ").strip()
    
    headers = {
        "Authorization": f"Bearer {agent_token}",
        "Accept": "application/json"
    }

    # 2. Mulai Monitoring Loop
    print("\n[2] Agent aktif! Memulai pengiriman Heartbeat dan Metrik...")
    while True:
        try:
            # Kirim Heartbeat
            resp = requests.post(f"{SIKANDI_URL}/heartbeat", headers=headers)
            if resp.status_code == 200:
                print(f"[{time.strftime('%H:%M:%S')}] Heartbeat sent.")
            else:
                print(f"Heartbeat error: {resp.status_code} {resp.text}")

            # Kirim Metrik Simulasi (Nilai Random)
            metrics = {
                "cpu_usage": round(random.uniform(10.0, 85.0), 1),
                "memory_usage": round(random.uniform(40.0, 90.0), 1),
                "disk_usage": 65.5
            }
            requests.post(f"{SIKANDI_URL}/metrics", headers=headers, json=metrics)
            print(f"[{time.strftime('%H:%M:%S')}] Metrics sent: CPU {metrics['cpu_usage']}% | RAM {metrics['memory_usage']}%")

            # Kirim status service simulasi
            requests.post(f"{SIKANDI_URL}/services", headers=headers, json={
                "services": [
                    {"name": "mysql", "status": "running"},
                    {"name": "nginx", "status": "running"}
                ]
            })

            time.sleep(10) # Loop tiap 10 detik agar cepat terlihat di demo
            
        except KeyboardInterrupt:
            print("\nAgent dihentikan.")
            break
        except Exception as e:
            print(f"Koneksi error: {e}")
            time.sleep(10)

if __name__ == "__main__":
    main()
