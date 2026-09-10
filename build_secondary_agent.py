import os

files = {
    "detectors/suspicious_network.py": """class SuspiciousNetworkDetector:
    def __init__(self):
        self.suspicious_ports = [4444, 5555, 6666, 7777, 9999, 31337] # Common backdoor/C2 ports
        
    def analyze(self, connections):
        alerts = []
        for c in connections:
            rport = c.get('raddr_port')
            if rport in self.suspicious_ports:
                alerts.append({
                    "event_type": "network",
                    "action": "suspicious_connection",
                    "source_ip": c.get('laddr_ip'),
                    "remote_ip": c.get('raddr_ip'),
                    "process_pid": c.get('pid'),
                    "reason": f"Connection to known suspicious port: {rport}",
                    "severity": "high"
                })
        return alerts
""",
    "detectors/persistence.py": """import os
import platform

class PersistenceDetector:
    def __init__(self):
        self.os_type = platform.system()
        self.known_crons = set()
        self.initialized = False
        
    def analyze(self, _): # Takes no input, it checks system state
        alerts = []
        if self.os_type == 'Linux':
            cron_dirs = ['/etc/crontab', '/etc/cron.d/', '/var/spool/cron/crontabs/']
            current_crons = set()
            for cdir in cron_dirs:
                if os.path.isfile(cdir):
                    current_crons.add(cdir)
                elif os.path.isdir(cdir):
                    try:
                        for f in os.listdir(cdir):
                            current_crons.add(os.path.join(cdir, f))
                    except Exception:
                        pass
            
            if not self.initialized:
                self.known_crons = current_crons
                self.initialized = True
            else:
                new_crons = current_crons - self.known_crons
                for nc in new_crons:
                    alerts.append({
                        "event_type": "persistence",
                        "action": "created",
                        "file": nc,
                        "reason": "New cron job / persistence mechanism detected",
                        "severity": "medium"
                    })
                self.known_crons = current_crons
                
        return alerts
""",
    "detectors/privilege_escalation.py": """class PrivilegeEscalationDetector:
    def __init__(self):
        self.seen_sudoers = set()
        
    def analyze(self, processes):
        alerts = []
        for p in processes:
            name = str(p.get('name', '')).lower()
            cmd = " ".join(p.get('cmdline', []) or []).lower()
            user = p.get('username')
            pid = p.get('pid')
            
            # Detect suspicious sudo usage or su
            if (name == 'sudo' or name == 'su') and pid not in self.seen_sudoers:
                self.seen_sudoers.add(pid)
                if 'bash' in cmd or 'sh' in cmd:
                    alerts.append({
                        "event_type": "privilege_escalation",
                        "action": "attempt",
                        "username": user,
                        "process_name": name,
                        "cmdline": cmd,
                        "reason": "User attempting to spawn root shell via sudo/su",
                        "severity": "medium"
                    })
                    
        # keep memory small
        if len(self.seen_sudoers) > 1000:
            self.seen_sudoers.clear()
            
        return alerts
""",
    "detectors/suspicious_login.py": """class SuspiciousLoginDetector:
    def __init__(self):
        self.known_ips = set()
        
    def analyze(self, events):
        alerts = []
        for ev in events:
            if ev.get('event_type') == 'login' and ev.get('action') in ['success', 'login_success']:
                ip = ev.get('source_ip')
                user = ev.get('username')
                if ip and ip not in self.known_ips:
                    # If this is the very first time we see an IP and it's successful, 
                    # we could flag it. But to avoid spam, we just record it.
                    # We flag if user is root and IP is new.
                    if user == 'root' and len(self.known_ips) > 0:
                        alerts.append({
                            "event_type": "suspicious_login",
                            "action": "detected",
                            "username": user,
                            "source_ip": ip,
                            "reason": "Root login from entirely new IP address",
                            "severity": "medium"
                        })
                    self.known_ips.add(ip)
        return alerts
""",
    "security/baseline.py": """# Keeps track of normal baseline states
class SystemBaseline:
    def __init__(self):
        self.data = {}
    
    def update(self, key, value):
        self.data[key] = value
        
    def get(self, key):
        return self.data.get(key)
""",
    "security/event.py": """import uuid
import time
import platform

class SecurityEventBuilder:
    @staticmethod
    def build(event_type, action, severity="info", **kwargs):
        ev = {
            "event_id": str(uuid.uuid4()),
            "timestamp": int(time.time()),
            "hostname": platform.node(),
            "event_type": event_type,
            "action": action,
            "severity": severity,
            "username": kwargs.get("username", "N/A"),
            "source_ip": kwargs.get("source_ip", "N/A"),
            "process_name": kwargs.get("process_name", "N/A"),
            "reason": kwargs.get("reason", "Anomalous behavior detected")
        }
        ev.update(kwargs)
        return ev
"""
}

def update_files(base_path):
    for rel_path, content in files.items():
        full_path = os.path.join(base_path, rel_path)
        with open(full_path, 'w') as f:
            f.write(content)
            
update_files('d:/Project/SIKANDI/sikandi-agent')
print("Agent secondary modules fully implemented.")
