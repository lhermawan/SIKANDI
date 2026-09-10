class RiskScorer:
    def __init__(self):
        self.scores = {
            'login_failed': 10,
            'brute_force_detected': 50,
            'suspicious_process_started': 25,
            'file_integrity_modified': 40,
            'file_integrity_deleted': 40,
            'network_suspicious_connection': 25,
            'persistence_created': 50,
            'privilege_escalation_attempt': 40,
            'suspicious_login_detected': 20,
            'correlation_privilege_escalation_after_suspicious_login': 80
        }
        
    def calculate(self, event):
        key = f"{event.get('event_type')}_{event.get('action')}"
        score = self.scores.get(key, 5)
        
        # clamp
        if score > 100: score = 100
        if score < 0: score = 0
        
        event['risk_score'] = score
        return event
