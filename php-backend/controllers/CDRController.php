<?php
/**
 * Vista CRM - CDR (Call Detail Records) Controller
 */

require_once __DIR__ . '/../models/Database.php';

class CDRController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where[] = 'DATE(cdr.start_time) >= ?';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = 'DATE(cdr.start_time) <= ?';
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['agent_id'])) {
            $where[] = 'cdr.agent_id = ?';
            $params[] = $filters['agent_id'];
        }

        if (!empty($filters['campaign_id'])) {
            $where[] = 'cdr.campaign_id = ?';
            $params[] = $filters['campaign_id'];
        }

        if (!empty($filters['call_status'])) {
            $where[] = 'cdr.call_status = ?';
            $params[] = $filters['call_status'];
        }

        $limit = $filters['limit'] ?? 100;
        $offset = $filters['offset'] ?? 0;

        $sql = "SELECT 
                    cdr.id,
                    cdr.uuid,
                    cdr.call_id,
                    cdr.agent_id,
                    u.full_name as agent_name,
                    cdr.campaign_id,
                    c.name as campaign_name,
                    cdr.phone_number,
                    cdr.call_type,
                    cdr.call_status,
                    d.name as disposition,
                    cdr.start_time,
                    cdr.answer_time,
                    cdr.end_time,
                    cdr.duration,
                    cdr.talk_time,
                    cdr.hold_time,
                    cdr.wrap_up_time,
                    cdr.recording_url,
                    cdr.notes
                FROM cdr
                LEFT JOIN users u ON u.id = cdr.agent_id
                LEFT JOIN campaigns c ON c.id = cdr.campaign_id
                LEFT JOIN dispositions d ON d.id = cdr.disposition_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY cdr.start_time DESC
                LIMIT {$limit} OFFSET {$offset}";

        return ['data' => $this->db->fetchAll($sql, $params), 'code' => 200];
    }

    public function create($data) {
        $cdrId = $this->db->insert('cdr', [
            'uuid' => $this->generateUUID(),
            'call_id' => $data['call_id'] ?? $this->generateUUID(),
            'agent_id' => $data['agent_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'phone_number' => $data['phone_number'],
            'caller_id' => $data['caller_id'] ?? null,
            'call_type' => $data['call_type'] ?? 'outbound',
            'call_status' => $data['call_status'] ?? 'dialing',
            'start_time' => date('Y-m-d H:i:s')
        ]);

        $cdr = $this->db->fetch("SELECT * FROM cdr WHERE id = ?", [$cdrId]);
        return ['data' => $cdr, 'code' => 201];
    }

    public function updateCallEnd($callId, $data) {
        $updateData = [
            'call_status' => $data['call_status'] ?? 'answered',
            'end_time' => date('Y-m-d H:i:s')
        ];

        if (isset($data['disposition_id'])) {
            $updateData['disposition_id'] = $data['disposition_id'];
        }
        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        // Calculate duration
        $cdr = $this->db->fetch("SELECT start_time, answer_time FROM cdr WHERE call_id = ?", [$callId]);
        if ($cdr) {
            $startTime = strtotime($cdr['start_time']);
            $endTime = time();
            $updateData['duration'] = $endTime - $startTime;

            if ($cdr['answer_time']) {
                $answerTime = strtotime($cdr['answer_time']);
                $updateData['talk_time'] = $endTime - $answerTime;
            }
        }

        $this->db->update('cdr', $updateData, 'call_id = :call_id', ['call_id' => $callId]);

        $updatedCdr = $this->db->fetch("SELECT * FROM cdr WHERE call_id = ?", [$callId]);
        return ['data' => $updatedCdr, 'code' => 200];
    }

    public function getStats($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where[] = 'DATE(start_time) >= ?';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = 'DATE(start_time) <= ?';
            $params[] = $filters['end_date'];
        }

        $stats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_calls,
                SUM(CASE WHEN call_status = 'answered' THEN 1 ELSE 0 END) as answered_calls,
                SUM(CASE WHEN call_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer_calls,
                SUM(CASE WHEN call_status = 'busy' THEN 1 ELSE 0 END) as busy_calls,
                SUM(CASE WHEN call_status = 'failed' THEN 1 ELSE 0 END) as failed_calls,
                SUM(duration) as total_duration,
                SUM(talk_time) as total_talk_time,
                AVG(duration) as avg_duration,
                AVG(talk_time) as avg_talk_time
            FROM cdr
            WHERE " . implode(' AND ', $where),
            $params
        );

        return ['data' => $stats, 'code' => 200];
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
