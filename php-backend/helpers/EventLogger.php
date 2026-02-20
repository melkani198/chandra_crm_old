<?php

class EventLogger {
    public static function record($aggregateType, $aggregateId, $eventType, $payload = []) {
        try {
            $db = Database::getInstance();
            $db->insert('event_log', [
                'aggregate_type' => $aggregateType,
                'aggregate_id' => (string) $aggregateId,
                'event_type' => $eventType,
                'payload_json' => json_encode($payload),
                'trace_id' => RequestContext::getTraceId(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Throwable $e) {
            error_log('event_log_write_failed: ' . $e->getMessage());
        }
    }
}
