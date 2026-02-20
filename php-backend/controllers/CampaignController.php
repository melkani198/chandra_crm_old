<?php
/**
 * Vista CRM - Campaign Controller
 */

require_once __DIR__ . '/../models/Database.php';

class CampaignController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['campaign_type'])) {
            $where[] = 'campaign_type = ?';
            $params[] = $filters['campaign_type'];
        }

        $sql = "SELECT c.*, u.full_name as created_by_name 
                FROM campaigns c 
                LEFT JOIN users u ON u.id = c.created_by 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY c.created_at DESC";

        return ['data' => $this->db->fetchAll($sql, $params), 'code' => 200];
    }

    public function getById($id) {
        $campaign = $this->db->fetch(
            "SELECT c.*, u.full_name as created_by_name 
             FROM campaigns c 
             LEFT JOIN users u ON u.id = c.created_by 
             WHERE c.id = ?",
            [$id]
        );

        if (!$campaign) {
            return ['error' => 'Campaign not found', 'code' => 404];
        }

        return ['data' => $campaign, 'code' => 200];
    }

    public function create($data, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        if (empty($data['name'])) {
            return ['error' => 'Campaign name is required', 'code' => 400];
        }

        $campaignId = $this->db->insert('campaigns', [
            'uuid' => $this->generateUUID(),
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'campaign_type' => $data['campaign_type'] ?? 'progressive',
            'status' => $data['status'] ?? 'active',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'did_number' => $data['did_number'] ?? null,
            'caller_id' => $data['caller_id'] ?? null,
            'acd_code' => $data['acd_code'] ?? null,
            'call_context' => $data['call_context'] ?? null,
            'max_dial_time' => $data['max_dial_time'] ?? 60,
            'pacing' => $data['pacing'] ?? '2.30',
            'wrap_up_time' => $data['wrap_up_time'] ?? 0,
            'script' => $data['script'] ?? null,
            'ivr_no' => $data['ivr_no'] ?? null,
            'created_by' => $currentUser['id']
        ]);

        return $this->getById($campaignId);
    }

    public function update($id, $data, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $allowed = ['name', 'description', 'campaign_type', 'status', 'start_date', 'end_date',
                    'did_number', 'caller_id', 'acd_code', 'call_context', 'max_dial_time',
                    'pacing', 'wrap_up_time', 'script', 'ivr_no'];
        $updateData = array_intersect_key($data, array_flip($allowed));

        if (empty($updateData)) {
            return ['error' => 'No valid fields to update', 'code' => 400];
        }

        $this->db->update('campaigns', $updateData, 'id = :id', ['id' => $id]);

        return $this->getById($id);
    }

    public function delete($id, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $result = $this->db->delete('campaigns', 'id = ?', [$id]);

        if ($result === 0) {
            return ['error' => 'Campaign not found', 'code' => 404];
        }

        return ['data' => ['message' => 'Campaign deleted successfully'], 'code' => 200];
    }

    public function getStats($id = null) {
        return ['data' => $this->db->callProcedure('sp_get_campaign_stats', [$id]), 'code' => 200];
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
