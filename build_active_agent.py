import os

files = {
    "collectors/processes.py": """import psutil
import time

class ProcessCollector:
    def collect(self):
        procs = []
        for p in psutil.process_iter(['pid', 'ppid', 'name', 'exe', 'cmdline', 'username']):
            try:
                info = p.info
                info['timestamp'] = time.time()
                procs.append(info)
            except (psutil.NoSuchProcess, psutil.AccessDenied):
                pass
        return procs
""",
    "collectors/network.py": """import psutil

class NetworkCollector:
    def collect(self):
        conns = []
        try:
            for c in psutil.net_connections(kind='inet'):
                if c.status == 'ESTABLISHED':
                    conns.append({
                        'laddr_ip': c.laddr.ip if c.laddr else None,
                        'laddr_port': c.laddr.port if c.laddr else None,
                        'raddr_ip': c.raddr.ip if c.raddr else None,
                        'raddr_port': c.raddr.port if c.raddr else None,
                        'status': c.status,
                        'pid': c.pid
                    })
        except Exception:
            pass
        return conns
""",
    "collectors/login_events.py": """import platform
import os
import time

class LoginCollector:
    def __init__(self):
        self.os_type = platform.system()
        self.last_pos = 0
        self.auth_file = '/var/log/auth.log' if os.path.exists('/var/log/auth.log') else '/var/log/secure'
        if self.os_type == 'Linux' and os.path.exists(self.auth_file):
            self.last_pos = os.path.getsize(self.auth_file)
            
    def collect(self):
        events = []
        if self.os_type == 'Linux' and os.path.exists(self.auth_file):
            try:
                with open(self.auth_file, 'r') as f:
                    f.seek(self.last_pos)
                    lines = f.readlines()
                    self.last_pos = f.tell()
                    for line in lines:
                        if 'sshd' in line:
                            if 'Failed password' in line:
                                parts = line.split()
                                user_idx = parts.index('for') + 1
                                # sometimes "invalid user" is present
                                if parts[user_idx] == 'invalid':
                                    user_idx += 2
                                user = parts[user_idx]
                                ip_idx = parts.index('from') + 1
                                ip = parts[ip_idx]
                                events.append({
                                    'event_type': 'login',
                                    'action': 'failed',
                                    'username': user,
                                    'source_ip': ip,
                                    'severity': 'low'
                                })
                            elif 'Accepted password' in line or 'Accepted publickey' in line:
                                parts = line.split()
                                user_idx = parts.index('for') + 1
                                user = parts[user_idx]
                                ip_idx = parts.index('from') + 1
                                ip = parts[ip_idx]
                                events.append({
                                    'event_type': 'login',
                                    'action': 'success',
                                    'username': user,
                                    'source_ip': ip,
                                    'severity': 'info'
                                })
            except Exception:
                pass
        return events
""",
    "collectors/file_integrity.py": """import os
import hashlib

class FIMCollector:
    def __init__(self, config):
        self.paths = config.get('security.file_integrity.paths', ['/etc/passwd', '/etc/shadow'])
        self.hashes = {}
        self._initialize_hashes()
        
    def _initialize_hashes(self):
        for path in self.paths:
            self.hashes[path] = self._hash_file(path)
            
    def _hash_file(self, path):
        if not os.path.exists(path):
            return None
        try:
            hasher = hashlib.sha256()
            with open(path, 'rb') as f:
                buf = f.read(65536)
                while len(buf) > 0:
                    hasher.update(buf)
                    buf = f.read(65536)
            return hasher.hexdigest()
        except Exception:
            return None
            
    def collect(self):
        events = []
        for path in self.paths:
            current_hash = self._hash_file(path)
            if self.hashes.get(path) != current_hash:
                if current_hash is None:
                    action = "deleted"
                elif self.hashes.get(path) is None:
                    action = "created"
                else:
                    action = "modified"
                    
                events.append({
                    "event_type": "file_integrity",
                    "action": action,
                    "file": path,
                    "old_hash": self.hashes.get(path),
                    "new_hash": current_hash,
                    "severity": "high"
                })
                self.hashes[path] = current_hash
        return events
""",
    "detectors/brute_force.py": """import time

class BruteForceDetector:
    def __init__(self, config):
        self.window = config.get('security.brute_force.window_seconds', 300)
        self.threshold = config.get('security.brute_force.threshold', 5)
        self.failed_logins = {} # ip -> list of timestamps
        
    def analyze(self, events):
        alerts = []
        now = time.time()
        for ev in events:
            if ev.get('event_type') == 'login' and ev.get('action') == 'failed':
                ip = ev.get('source_ip')
                if not ip: continue
                if ip not in self.failed_logins:
                    self.failed_logins[ip] = []
                self.failed_logins[ip].append(now)
                
                # Cleanup old
                self.failed_logins[ip] = [t for t in self.failed_logins[ip] if now - t <= self.window]
                
                count = len(self.failed_logins[ip])
                if count >= self.threshold:
                    alerts.append({
                        "event_type": "brute_force",
                        "action": "detected",
                        "source_ip": ip,
                        "username": ev.get('username'),
                        "count": count,
                        "severity": "high" if count < 20 else "critical"
                    })
        return alerts
""",
    "detectors/suspicious_process.py": """class SuspiciousProcessDetector:
    def __init__(self):
        self.suspicious_names = ['nc', 'ncat', 'socat', 'mimikatz.exe', 'powershell.exe', 'cmd.exe', 'wget', 'curl']
        self.seen_pids = set()
        
    def analyze(self, processes):
        alerts = []
        for p in processes:
            pid = p.get('pid')
            if pid in self.seen_pids:
                continue
            self.seen_pids.add(pid)
            
            name = str(p.get('name', '')).lower()
            cmdline = " ".join(p.get('cmdline', []) or []).lower()
            
            if name in self.suspicious_names or 'reverse_tcp' in cmdline or 'stratum' in cmdline:
                alerts.append({
                    "event_type": "suspicious_process",
                    "action": "started",
                    "process_name": name,
                    "cmdline": cmdline,
                    "username": p.get('username'),
                    "severity": "medium"
                })
        return alerts
""",
    "security/risk_score.py": """class RiskScorer:
    def __init__(self):
        self.scores = {
            'login_failed': 10,
            'brute_force_detected': 50,
            'suspicious_process_started': 25,
            'file_integrity_modified': 40,
            'file_integrity_deleted': 40
        }
        
    def calculate(self, event):
        key = f"{event.get('event_type')}_{event.get('action')}"
        score = self.scores.get(key, 5)
        
        # clamp
        if score > 100: score = 100
        if score < 0: score = 0
        
        event['risk_score'] = score
        return event
"""
}

def update_files(base_path):
    for rel_path, content in files.items():
        full_path = os.path.join(base_path, rel_path)
        with open(full_path, 'w') as f:
            f.write(content)
            
update_files('d:/Project/SIKANDI/sikandi-agent')
print("Agent modules updated with active logic.")
