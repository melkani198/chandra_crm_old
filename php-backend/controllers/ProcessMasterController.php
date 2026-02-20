<?php
/**
 * Vista CRM - Process Master Controller (Dispositions, Break Types, Settings)
 */

require_once __DIR__ . '/../models/Database.php';

class ProcessMasterController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ==================== PROCESS MASTER ====================
    
    public function getAllProcesses() {
        $processes = $this->db->fetchAll("SELECT * FROM process_master ORDER BY name");
        return ['data' => $processes, 'code' => 200];
    }

    public function getProcessById($id) {
        $process = $this->db->fetch("SELECT * FROM process_master WHERE id = ?", [$id]);
        if (!$process) {
            return ['error' => 'Process not found', 'code' => 404];
        }
        return ['data' => $process, 'code' => 200];
    }

    public function createProcess($data, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $processId = $this->db->insert('process_master', [
            'uuid' => $this->generateUUID(),
            'name' => $data['name'] ?? 'Default Process',
            'description' => $data['description'] ?? '',
            'auto_callback_max' => $data['auto_callback_max'] ?? 60,
            'callback_check_interval' => $data['callback_check_interval'] ?? 30,
            'caller_id_popup_len' => $data['caller_id_popup_len'] ?? 0,
            'callback_popup_time' => $data['callback_popup_time'] ?? 5,
            'auto_logout_idle_time' => $data['auto_logout_idle_time'] ?? 0,
            'allowed_country_code' => $data['allowed_country_code'] ?? null,
            'dial_option' => $data['dial_option'] ?? 'default',
            'recording_folder' => $data['recording_folder'] ?? null,
            'auto_dial_idle' => $data['auto_dial_idle'] ?? 0,
            'auto_save' => $data['auto_save'] ?? 0,
            'backend_process' => $data['backend_process'] ?? 0,
            'manual_dialing' => $data['manual_dialing'] ?? 1,
            'agent_can_dial_previous' => $data['agent_can_dial_previous'] ?? 0,
            'edit_phone_no' => $data['edit_phone_no'] ?? 0,
            'hide_phone_on_agent' => $data['hide_phone_on_agent'] ?? 0,
            'dial_number_randomly' => $data['dial_number_randomly'] ?? 0,
            'dial_alternate_no' => $data['dial_alternate_no'] ?? 0,
            'agent_to_set_callback' => $data['agent_to_set_callback'] ?? 1,
            'agent_to_set_dnc' => $data['agent_to_set_dnc'] ?? 0,
            'agent_to_transfer' => $data['agent_to_transfer'] ?? 0,
            'last_campaign_save' => $data['last_campaign_save'] ?? 0
        ]);

        return $this->getProcessById($processId);
    }

    public function updateProcess($id, $data, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $allowed = ['name', 'description', 'auto_callback_max', 'callback_check_interval',
                    'caller_id_popup_len', 'callback_popup_time', 'auto_logout_idle_time',
                    'allowed_country_code', 'dial_option', 'recording_folder', 'auto_dial_idle',
                    'auto_save', 'backend_process', 'manual_dialing', 'agent_can_dial_previous',
                    'edit_phone_no', 'hide_phone_on_agent', 'dial_number_randomly',
                    'dial_alternate_no', 'agent_to_set_callback', 'agent_to_set_dnc',
                    'agent_to_transfer', 'last_campaign_save'];
        $updateData = array_intersect_key($data, array_flip($allowed));

        if (!empty($updateData)) {
            $this->db->update('process_master', $updateData, 'id = :id', ['id' => $id]);
        }

        return $this->getProcessById($id);
    }

    // ==================== DISPOSITIONS ====================

    public function getAllDispositions() {
        $dispositions = $this->db->fetchAll(
            "SELECT * FROM dispositions WHERE is_active = 1 ORDER BY sort_order, name"
        );
        return ['data' => $dispositions, 'code' => 200];
    }

    public function createDisposition($data, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        if (empty($data['name']) || empty($data['code'])) {
            return ['error' => 'Name and code are required', 'code' => 400];
        }

        $dispId = $this->db->insert('dispositions', [
            'uuid' => $this->generateUUID(),
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? '',
            'is_callback' => $data['is_callback'] ?? 0,
            'is_dnc' => $data['is_dnc'] ?? 0,
            'color' => $data['color'] ?? '#3b82f6'
        ]);

        $disp = $this->db->fetch("SELECT * FROM dispositions WHERE id = ?", [$dispId]);
        return ['data' => $disp, 'code' => 201];
    }

    public function deleteDisposition($id, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $result = $this->db->delete('dispositions', 'id = ?', [$id]);
        if ($result === 0) {
            return ['error' => 'Disposition not found', 'code' => 404];
        }

        return ['data' => ['message' => 'Disposition deleted successfully'], 'code' => 200];
    }

    // ==================== BREAK TYPES ====================

    public function getAllBreakTypes() {
        $breakTypes = $this->db->fetchAll(
            "SELECT * FROM break_types WHERE is_active = 1 ORDER BY name"
        );
        return ['data' => $breakTypes, 'code' => 200];
    }

    public function createBreakType($data, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        if (empty($data['name']) || empty($data['code'])) {
            return ['error' => 'Name and code are required', 'code' => 400];
        }

        $breakId = $this->db->insert('break_types', [
            'uuid' => $this->generateUUID(),
            'name' => $data['name'],
            'code' => $data['code'],
            'max_duration' => $data['max_duration'] ?? 15,
            'is_paid' => $data['is_paid'] ?? 1,
            'color' => $data['color'] ?? '#f59e0b'
        ]);

        $breakType = $this->db->fetch("SELECT * FROM break_types WHERE id = ?", [$breakId]);
        return ['data' => $breakType, 'code' => 201];
    }

    public function deleteBreakType($id, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $result = $this->db->delete('break_types', 'id = ?', [$id]);
        if ($result === 0) {
            return ['error' => 'Break type not found', 'code' => 404];
        }

        return ['data' => ['message' => 'Break type deleted successfully'], 'code' => 200];
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
