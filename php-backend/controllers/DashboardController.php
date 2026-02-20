<?php
/**
 * Vista CRM - Dashboard Controller (Live Monitoring, Stats)
 */

require_once __DIR__ . '/../models/Database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getStats() {
        // Get agent status counts
        $agentStats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_agents,
                SUM(CASE WHEN status = 'break' THEN 1 ELSE 0 END) as agents_on_break,
                SUM(CASE WHEN status IN ('idle', 'ready') THEN 1 ELSE 0 END) as agents_idle,
                SUM(CASE WHEN status = 'manual_on' THEN 1 ELSE 0 END) as agents_manual_on,
                SUM(CASE WHEN status = 'on_call' THEN 1 ELSE 0 END) as agents_on_call,
                SUM(CASE WHEN status = 'wrap_up' THEN 1 ELSE 0 END) as agents_wrap_up,
                SUM(CASE WHEN status = 'ringing' THEN 1 ELSE 0 END) as agents_ringing
            FROM agent_status 
            WHERE status != 'offline'
        ");

        // Get today's call stats
        $callStats = $this->db->fetch("
            SELECT 
                COUNT(*) as dialed_calls,
                SUM(CASE WHEN call_status = 'answered' THEN 1 ELSE 0 END) as connected_calls,
                SUM(CASE WHEN call_status = 'abandoned' THEN 1 ELSE 0 END) as abandoned_calls,
                SUM(CASE WHEN call_status = 'no_answer' THEN 1 ELSE 0 END) as missed_calls,
                AVG(CASE WHEN call_status = 'answered' THEN talk_time ELSE NULL END) as avg_talk_time,
                AVG(duration) as avg_handling_time,
                AVG(wrap_up_time) as avg_wrap_up_time
            FROM cdr 
            WHERE DATE(start_time) = CURDATE()
        ");

        // Calculate percentages
        $dialedCalls = $callStats['dialed_calls'] ?? 0;
        $connectedCalls = $callStats['connected_calls'] ?? 0;
        $abandonedCalls = $callStats['abandoned_calls'] ?? 0;

        $connectivityPercentage = $dialedCalls > 0 ? round(($connectedCalls / $dialedCalls) * 100, 2) : 0;
        $dropPercentage = $dialedCalls > 0 ? round(($abandonedCalls / $dialedCalls) * 100, 2) : 0;

        return ['data' => [
            'total_agents' => (int)($agentStats['total_agents'] ?? 0),
            'agents_on_break' => (int)($agentStats['agents_on_break'] ?? 0),
            'agents_idle' => (int)($agentStats['agents_idle'] ?? 0),
            'agents_manual_on' => (int)($agentStats['agents_manual_on'] ?? 0),
            'agents_on_call' => (int)($agentStats['agents_on_call'] ?? 0),
            'agents_wrap_up' => (int)($agentStats['agents_wrap_up'] ?? 0),
            'agents_ringing' => (int)($agentStats['agents_ringing'] ?? 0),
            'queue_count' => 0,
            'queue_time' => '00:00',
            'on_hold' => 0,
            'avg_handling_time' => $this->formatDuration($callStats['avg_handling_time'] ?? 0),
            'avg_talk_time' => $this->formatDuration($callStats['avg_talk_time'] ?? 0),
            'avg_wrap_up_time' => $this->formatDuration($callStats['avg_wrap_up_time'] ?? 0),
            'dialed_calls' => (int)$dialedCalls,
            'connected_calls' => (int)$connectedCalls,
            'answered_calls' => (int)$connectedCalls,
            'abandoned_calls' => (int)$abandonedCalls,
            'connectivity_percentage' => $connectivityPercentage,
            'drop_percentage' => $dropPercentage,
            'sla_percentage' => 0,
            'offered_calls' => (int)$dialedCalls,
            'ivr_missed' => 0,
            'total_sms' => 0
        ], 'code' => 200];
    }

    public function getLiveAgents($campaignId = null) {
        $where = "ast.status != 'offline'";
        $params = [];

        if ($campaignId) {
            $where .= " AND ast.campaign_id = ?";
            $params[] = $campaignId;
        }

        $agents = $this->db->fetchAll("
            SELECT 
                u.id as agent_id,
                u.full_name as agent_name,
                u.extension,
                ast.status,
                c.id as campaign_id,
                c.name as campaign_name,
                bt.name as break_type,
                ast.phone_number as phone,
                TIMEDIFF(NOW(), ast.status_start_time) as current_duration
            FROM agent_status ast
            JOIN users u ON u.id = ast.agent_id
            LEFT JOIN campaigns c ON c.id = ast.campaign_id
            LEFT JOIN break_types bt ON bt.id = ast.break_type_id
            WHERE {$where}
            ORDER BY u.full_name
        ", $params);

        // Format duration
        foreach ($agents as &$agent) {
            $agent['current_duration'] = $agent['current_duration'] ?? '00:00:00';
        }

        return ['data' => $agents, 'code' => 200];
    }

    private function formatDuration($seconds) {
        if (!$seconds) return '00:00:00';
        $seconds = (int)$seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
?>
