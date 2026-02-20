<?php

class AuditLogger {
    public static function record($actorUserId, $action, $targetType, $targetId = null, $metadata = []) {
        try {
            $db = Database::getInstance();
            $db->insert('audit_log', [
                'actor_user_id' => $actorUserId,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId !== null ? (string) $targetId : null,
                'metadata_json' => json_encode($metadata),
                'trace_id' => RequestContext::getTraceId(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Throwable $e) {
            error_log('audit_log_write_failed: ' . $e->getMessage());
        }
    }
}
