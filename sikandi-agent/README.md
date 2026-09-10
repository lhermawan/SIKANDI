# SIKANDI Intelligent Security Monitoring Agent

Agent berbasis Python untuk monitoring sistem dan keamanan host secara realtime. 

## Fitur
- System Monitoring (CPU, RAM, Disk)
- Security Event Monitoring (Login, Brute Force, Privilege Escalation)
- Process & Network Monitoring
- File Integrity Monitoring
- Offline Queue & Event Deduplication

## Cara Menjalankan
1. `pip install -r requirements.txt`
2. Edit `config.yaml` dan masukkan Token Agent
3. Jalankan `python agent.py`

## Test Mode
Jalankan `python agent.py --test-security` untuk mencoba deteksi event simulasi tanpa serangan sungguhan.
