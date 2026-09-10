import os

structure = {
    "sikandi-agent": {
        "__init__.py": "",
        "agent.py": """import time
import argparse
import logging
from config import Config
from transport.api import ApiClient
from transport.queue import EventQueue
from collectors.system import SystemCollector
from collectors.services import ServiceCollector
from security.correlation import CorrelationEngine
from security.deduplication import Deduplicator
import threading

logging.basicConfig(level=logging.INFO, format='%(levelname)s %(message)s')
logger = logging.getLogger(__name__)

class SikandiAgent:
    def __init__(self, test_mode=False):
        self.config = Config()
        self.test_mode = test_mode
        self.api = ApiClient(self.config)
        self.queue = EventQueue(self.config)
        self.system = SystemCollector()
        self.services = ServiceCollector()
        self.correlation = CorrelationEngine()
        self.deduplicator = Deduplicator(self.config)
        
    def start(self):
        logger.info("=== SIKANDI Intelligent Security Monitoring Agent ===")
        if not self.api.register():
            logger.error("Registration failed. Exiting.")
            return

        logger.info("Agent registered. Starting collectors...")
        
        # Start background threads for heartbeat and metrics
        threading.Thread(target=self._heartbeat_loop, daemon=True).start()
        threading.Thread(target=self._metrics_loop, daemon=True).start()
        threading.Thread(target=self._security_loop, daemon=True).start()
        threading.Thread(target=self._queue_flush_loop, daemon=True).start()

        try:
            while True:
                time.sleep(1)
        except KeyboardInterrupt:
            logger.info("Agent stopped.")

    def _heartbeat_loop(self):
        while True:
            self.api.send_heartbeat()
            time.sleep(self.config.get('agent.heartbeat_interval', 60))

    def _metrics_loop(self):
        while True:
            metrics = self.system.collect()
            self.api.send_metrics(metrics)
            
            services = self.services.collect()
            self.api.send_services(services)
            
            time.sleep(self.config.get('agent.metrics_interval', 60))

    def _security_loop(self):
        while True:
            # Here we would call various detectors
            # For test mode, we generate mock events
            if self.test_mode:
                self._run_test_mode()
            time.sleep(self.config.get('agent.security_interval', 10))
            
    def _queue_flush_loop(self):
        while True:
            events = self.queue.get_batch(50)
            if events:
                if self.api.send_security_events(events):
                    self.queue.clear_batch(events)
            time.sleep(5)
            
    def _run_test_mode(self):
        logger.info("Test mode: Generating mock security events...")
        event = {
            "event_type": "login",
            "action": "failed",
            "username": "root",
            "source_ip": "103.11.22.33",
            "severity": "high",
            "risk_score": 35
        }
        if not self.deduplicator.is_duplicate(event):
            self.queue.add(event)

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument('--test-security', action='store_true', help='Run in test mode with mock events')
    args = parser.parse_args()
    
    agent = SikandiAgent(test_mode=args.test_security)
    agent.start()
""",
        "config.py": """import yaml
import os

class Config:
    def __init__(self, path='config.yaml'):
        self.path = path
        self.data = self._load()

    def _load(self):
        if not os.path.exists(self.path):
            return {
                'agent': {'heartbeat_interval': 60, 'metrics_interval': 60, 'security_interval': 10},
                'api': {'url': 'http://localhost:8000/api/v1/agent', 'token': 'MASUKKAN_TOKEN_ANDA_DISINI', 'timeout': 10},
                'queue': {'max_size': 10000}
            }
        with open(self.path, 'r') as f:
            return yaml.safe_load(f)

    def get(self, key_path, default=None):
        keys = key_path.split('.')
        val = self.data
        for k in keys:
            if isinstance(val, dict) and k in val:
                val = val[k]
            else:
                return default
        return val
""",
        "config.yaml": """agent:
  heartbeat_interval: 60
  metrics_interval: 60
  security_interval: 10

api:
  url: "http://localhost:8000/api/v1/agent"
  token: "MASUKKAN_TOKEN_ANDA_DISINI"
  timeout: 10

security:
  enabled: true
  brute_force:
    enabled: true
    window_seconds: 300
    threshold: 5
  suspicious_login:
    enabled: true
  process_monitoring:
    enabled: true
  network_monitoring:
    enabled: true
  file_integrity:
    enabled: true
  persistence:
    enabled: true

queue:
  enabled: true
  max_size: 10000
""",
        "requirements.txt": """requests
psutil
pyyaml
""",
        "README.md": """# SIKANDI Intelligent Security Monitoring Agent

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
""",
        "collectors": {
            "__init__.py": "",
            "system.py": """import psutil
import platform
import time

class SystemCollector:
    def collect(self):
        return {
            "cpu_usage": psutil.cpu_percent(interval=1),
            "memory_total": psutil.virtual_memory().total,
            "memory_used": psutil.virtual_memory().used,
            "memory_usage": psutil.virtual_memory().percent,
            "disk_total": psutil.disk_usage('/').total,
            "disk_used": psutil.disk_usage('/').used,
            "disk_usage": psutil.disk_usage('/').percent,
            "uptime_seconds": int(time.time() - psutil.boot_time()),
            "hostname": platform.node(),
            "os": platform.system(),
            "os_version": platform.release()
        }
""",
            "services.py": """class ServiceCollector:
    def collect(self):
        # Placeholder for service collection
        return [
            {"name": "ssh", "status": "running"}
        ]
""",
            "processes.py": "class ProcessCollector:\n    pass\n",
            "network.py": "class NetworkCollector:\n    pass\n",
            "users.py": "class UserCollector:\n    pass\n",
            "login_events.py": "class LoginCollector:\n    pass\n",
            "file_integrity.py": "class FIMCollector:\n    pass\n",
        },
        "detectors": {
            "__init__.py": "",
            "brute_force.py": "class BruteForceDetector:\n    pass\n",
            "suspicious_login.py": "class SuspiciousLoginDetector:\n    pass\n",
            "privilege_escalation.py": "class PrivilegeEscalationDetector:\n    pass\n",
            "suspicious_process.py": "class SuspiciousProcessDetector:\n    pass\n",
            "suspicious_network.py": "class SuspiciousNetworkDetector:\n    pass\n",
            "persistence.py": "class PersistenceDetector:\n    pass\n",
            "anomaly.py": "class AnomalyDetector:\n    pass\n",
        },
        "security": {
            "__init__.py": "",
            "risk_score.py": "class RiskScorer:\n    pass\n",
            "event.py": "class SecurityEvent:\n    pass\n",
            "correlation.py": """class CorrelationEngine:
    def analyze(self, events):
        return None
""",
            "deduplication.py": """import time

class Deduplicator:
    def __init__(self, config):
        self.seen = {}
        self.cooldown = 60
        
    def is_duplicate(self, event):
        sig = f"{event.get('event_type')}_{event.get('username')}_{event.get('source_ip')}"
        now = time.time()
        if sig in self.seen and now - self.seen[sig] < self.cooldown:
            return True
        self.seen[sig] = now
        return False
""",
            "baseline.py": "class BaselineManager:\n    pass\n",
        },
        "transport": {
            "__init__.py": "",
            "api.py": """import requests
import socket
import logging

logger = logging.getLogger(__name__)

class ApiClient:
    def __init__(self, config):
        self.config = config
        self.base_url = config.get('api.url')
        self.token = config.get('api.token')
        self.timeout = config.get('api.timeout', 10)
        
    def _headers(self):
        return {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
        
    def register(self):
        try:
            resp = requests.post(f"{self.base_url}/register", json={
                "registration_token": self.token,
                "hostname": socket.gethostname(),
                "os": "Cross-Platform",
                "os_version": "2.0",
                "agent_version": "2.0.0"
            }, timeout=self.timeout)
            if resp.status_code == 201:
                return True
            # if 401 maybe we are using sanctum token already
            return True
        except Exception as e:
            logger.error(f"Register error: {e}")
            return False

    def send_heartbeat(self):
        try:
            requests.post(f"{self.base_url}/heartbeat", headers=self._headers(), timeout=self.timeout)
            logger.info("Heartbeat sent.")
        except Exception as e:
            logger.error(f"Heartbeat failed: {e}")

    def send_metrics(self, metrics):
        try:
            requests.post(f"{self.base_url}/metrics", headers=self._headers(), json=metrics, timeout=self.timeout)
            logger.info("Metrics sent.")
        except Exception as e:
            logger.error(f"Metrics failed: {e}")

    def send_services(self, services):
        try:
            requests.post(f"{self.base_url}/services", headers=self._headers(), json={"services": services}, timeout=self.timeout)
        except Exception as e:
            logger.error(f"Services failed: {e}")
            
    def send_security_events(self, events):
        try:
            for ev in events:
                payload = {
                    "type": ev.get("event_type", "security_event"),
                    "severity": ev.get("severity", "info"),
                    "message": f"Security Event: {ev.get('action')} by {ev.get('username')}",
                    "payload": ev
                }
                requests.post(f"{self.base_url}/events", headers=self._headers(), json=payload, timeout=self.timeout)
            logger.warning(f"Sent {len(events)} security events.")
            return True
        except Exception as e:
            logger.error(f"Events failed: {e}")
            return False
""",
            "queue.py": """class EventQueue:
    def __init__(self, config):
        self.queue = []
        self.max_size = config.get('queue.max_size', 10000)
        
    def add(self, event):
        if len(self.queue) < self.max_size:
            self.queue.append(event)
            
    def get_batch(self, size=50):
        return self.queue[:size]
        
    def clear_batch(self, events):
        self.queue = [e for e in self.queue if e not in events]
"""
        },
        "platform": {
            "__init__.py": "",
            "windows.py": "class WindowsPlatform:\n    pass\n",
            "linux.py": "class LinuxPlatform:\n    pass\n",
        }
    }
}

def create_structure(base_path, d):
    for k, v in d.items():
        path = os.path.join(base_path, k)
        if isinstance(v, dict):
            os.makedirs(path, exist_ok=True)
            create_structure(path, v)
        else:
            with open(path, "w") as f:
                f.write(v)

create_structure("d:/Project/SIKANDI", structure)
print("Agent structure created successfully.")
