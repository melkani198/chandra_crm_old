CREATE TABLE IF NOT EXISTS event_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aggregate_type VARCHAR(100) NOT NULL,
    aggregate_id VARCHAR(100) NOT NULL,
    event_type VARCHAR(150) NOT NULL,
    payload_json JSON NULL,
    trace_id VARCHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_event_aggregate (aggregate_type, aggregate_id),
    KEY idx_event_type_created (event_type, created_at),
    KEY idx_event_trace (trace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
