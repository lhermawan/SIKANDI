import time
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
