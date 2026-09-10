class CorrelationEngine:
    def __init__(self):
        self.event_window = []
        self.window_size = 60 # Keep events for 60 seconds for correlation
        
    def analyze(self, current_events):
        alerts = []
        import time
        now = time.time()
        
        # Add new events to the window
        for ev in current_events:
            ev['_timestamp'] = now
            self.event_window.append(ev)
            
        # Clean up old events
        self.event_window = [e for e in self.event_window if now - e.get('_timestamp', now) <= self.window_size]
        
        # Correlate: Suspicious login followed by Privilege Escalation
        for ev in current_events:
            if ev.get('event_type') == 'privilege_escalation':
                user = ev.get('username')
                # Check if this user had a suspicious login recently
                recent_login = next((e for e in self.event_window if e.get('event_type') == 'suspicious_login' and e.get('username') == user), None)
                if recent_login:
                    alerts.append({
                        "event_type": "correlation",
                        "action": "privilege_escalation_after_suspicious_login",
                        "username": user,
                        "reason": f"User {user} escalated privileges shortly after a suspicious login",
                        "severity": "critical"
                    })
                    
        return alerts
