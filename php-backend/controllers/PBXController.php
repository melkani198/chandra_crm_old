<?php
/**
 * Vista CRM - PBX Controller (Agent Actions, Dialing)
 */

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers/PBXClient.php';

class PBXController {
    private $db;
    private $pbx;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pbx = new PBXClient();
    }

    public function updateAgentStatus($data, $currentUser) {
        $validation = ApiValidator::validateAgentStatusPayload($data);
        if (!$validation['valid']) {
            return [
                'error' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
                'field_errors' => $validation['errors'],
                'code' => 422
            ];
        }

        $action = $data['action'];
        $extension = $data['extension'] ?? $currentUser['extension'];
        $campaignId = $data['campaign_id'] ?? null;
        $breakTypeCode = $data['break_type'] ?? null;

        $breakTypeId = null;
        if ($breakTypeCode) {
            $breakType = $this->db->fetch("SELECT id FROM break_types WHERE code = ?", [$breakTypeCode]);
            $breakTypeId = $breakType['id'] ?? null;
        }

        $statusMap = [
            'login' => 'idle',
            'logout' => 'offline',
            'ready' => 'ready',
            'break' => 'break',
            'manual_on' => 'manual_on',
            'manual_off' => 'idle',
            'on_call' => 'on_call',
            'wrap_up' => 'wrap_up'
        ];
        $status = $statusMap[$action] ?? 'idle';

        $this->db->query("
            INSERT INTO agent_status (agent_id, status, extension, campaign_id, break_type_id, status_start_time)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                extension = VALUES(extension),
                campaign_id = VALUES(campaign_id),
                break_type_id = VALUES(break_type_id),
                status_start_time = NOW()
        ", [$currentUser['id'], $status, $extension, $campaignId, $breakTypeId]);

        $pbxResponse = null;
        if ($this->pbx->isConfigured()) {
            switch ($action) {
                case 'login':
                    $pbxResponse = $this->pbx->agentLogin($extension, $campaignId);
                    break;
                case 'logout':
                    $pbxResponse = $this->pbx->agentLogout($extension);
                    break;
                case 'ready':
                    $pbxResponse = $this->pbx->agentReady($extension, $campaignId);
                    break;
                case 'break':
                    $pbxResponse = $this->pbx->agentBreak($extension, $breakTypeCode);
                    break;
                case 'manual_on':
                    $pbxResponse = $this->pbx->agentManualOn($extension);
                    break;
                case 'manual_off':
                    $pbxResponse = $this->pbx->agentManualOff($extension);
                    break;
            }
        }

        AuditLogger::record($currentUser['id'], 'agent.status.update', 'agent_status', $currentUser['id'], [
            'action' => $action,
            'status' => $status,
            'campaign_id' => $campaignId
        ]);
        EventLogger::record('agent', $currentUser['id'], 'agent.status.updated', [
            'action' => $action,
            'status' => $status,
            'campaign_id' => $campaignId,
            'extension' => $extension
        ]);

        return ['data' => [
            'message' => 'Status updated',
            'status' => $status,
            'pbx_response' => $pbxResponse
        ], 'code' => 200];
    }

    public function dial($data, $currentUser) {
        $validation = ApiValidator::validateDialPayload($data);
        if (!$validation['valid']) {
            return [
                'error' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
                'field_errors' => $validation['errors'],
                'code' => 422
            ];
        }

        $mode = $data['mode'] ?? 'progressive';
        $phone = $data['phone'];
        $campaignId = $data['campaign_id'] ?? null;
        $extension = $data['agent_extension'] ?? $currentUser['extension'];
        $callerId = $data['caller_id'] ?? null;

        $callId = uniqid('call_');
        $this->db->insert('cdr', [
            'uuid' => $this->generateUUID(),
            'call_id' => $callId,
            'agent_id' => $currentUser['id'],
            'campaign_id' => $campaignId,
            'phone_number' => $phone,
            'caller_id' => $callerId,
            'call_type' => $mode,
            'call_status' => 'dialing',
            'start_time' => date('Y-m-d H:i:s')
        ]);

        $this->db->query("
            UPDATE agent_status SET status = 'on_call', phone_number = ?, status_start_time = NOW()
            WHERE agent_id = ?
        ", [$phone, $currentUser['id']]);

        $pbxResponse = null;
        if ($this->pbx->isConfigured()) {
            $pbxResponse = $this->pbx->dial($mode, $phone, $extension, $callerId);
        }

        AuditLogger::record($currentUser['id'], 'call.dial', 'cdr', $callId, [
            'phone' => $phone,
            'mode' => $mode,
            'campaign_id' => $campaignId
        ]);
        EventLogger::record('call', $callId, 'call.dial_initiated', [
            'agent_id' => $currentUser['id'],
            'campaign_id' => $campaignId,
            'phone' => $phone,
            'mode' => $mode
        ]);

        return ['data' => [
            'message' => 'Dial initiated',
            'call_id' => $callId,
            'pbx_response' => $pbxResponse
        ], 'code' => 200];
    }

    public function hangup($currentUser) {
        $extension = $currentUser['extension'];

        $this->db->query("
            UPDATE agent_status SET status = 'wrap_up', phone_number = NULL, status_start_time = NOW()
            WHERE agent_id = ?
        ", [$currentUser['id']]);

        $this->db->query("
            UPDATE cdr SET
                call_status = 'answered',
                end_time = NOW(),
                duration = TIMESTAMPDIFF(SECOND, start_time, NOW()),
                talk_time = CASE WHEN answer_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, answer_time, NOW()) ELSE 0 END
            WHERE agent_id = ? AND end_time IS NULL
            ORDER BY start_time DESC LIMIT 1
        ", [$currentUser['id']]);

        $pbxResponse = null;
        if ($this->pbx->isConfigured()) {
            $pbxResponse = $this->pbx->hangup($extension);
        }

        return ['data' => [
            'message' => 'Call ended',
            'pbx_response' => $pbxResponse
        ], 'code' => 200];
    }

    public function transfer($targetExtension, $currentUser) {
        $extension = $currentUser['extension'];

        $pbxResponse = null;
        if ($this->pbx->isConfigured()) {
            $pbxResponse = $this->pbx->transfer($extension, $targetExtension);
        }

        $this->db->query("
            UPDATE agent_status SET status = 'idle', phone_number = NULL
            WHERE agent_id = ?
        ", [$currentUser['id']]);

        return ['data' => [
            'message' => 'Transfer initiated',
            'pbx_response' => $pbxResponse
        ], 'code' => 200];
    }

    public function barge($agentExtension, $currentUser) {
        if ($currentUser['role'] !== 'admin' && $currentUser['role'] !== 'supervisor') {
            return ['error' => 'Supervisor access required', 'code' => 403, 'error_code' => 'FORBIDDEN'];
        }

        $pbxResponse = null;
        if ($this->pbx->isConfigured()) {
            $pbxResponse = $this->pbx->barge($currentUser['extension'], $agentExtension);
        }

        return ['data' => [
            'message' => 'Barge initiated',
            'pbx_response' => $pbxResponse
        ], 'code' => 200];
    }

    public function whisper($agentExtension, $currentUser) {
        if ($currentUser['role'] !== 'admin' && $currentUser['role'] !== 'supervisor') {
            return ['error' => 'Supervisor access required', 'code' => 403, 'error_code' => 'FORBIDDEN'];
        }

        $pbxResponse = null;
        if ($this->pbx->isConfigured()) {
            $pbxResponse = $this->pbx->whisper($currentUser['extension'], $agentExtension);
        }

        return ['data' => [
            'message' => 'Whisper initiated',
            'pbx_response' => $pbxResponse
        ], 'code' => 200];
    }

    public function getMeta() {
        if (!$this->pbx->isConfigured()) {
            return ['error' => 'PBX not configured', 'code' => 400, 'error_code' => 'PBX_NOT_CONFIGURED'];
        }

        $response = $this->pbx->getMetaInit();
        return ['data' => $response, 'code' => 200];
    }

    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
