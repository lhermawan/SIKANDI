import requests
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
