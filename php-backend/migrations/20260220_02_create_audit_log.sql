CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_user_id BIGINT UNSIGNED NULL,
    action VARCHAR(150) NOT NULL,
    target_type VARCHAR(100) NOT NULL,
    target_id VARCHAR(100) NULL,
    metadata_json JSON NULL,
    trace_id VARCHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_actor_created (actor_user_id, created_at),
    KEY idx_audit_target (target_type, target_id),
    KEY idx_audit_action_created (action, created_at),
    KEY idx_audit_trace (trace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
