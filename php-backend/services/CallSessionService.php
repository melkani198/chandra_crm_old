<?php

class CallSessionService
{
    public static function create($data)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO call_sessions 
            (call_id, agent_id, campaign_id, contact_id, phone_number, direction, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['call_id'],
            $data['agent_id'],
            $data['campaign_id'],
            $data['contact_id'],
            $data['phone'],
            $data['direction'],
            $data['status']
        ]);
    }

    public static function updateStatus($callId, $status)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            UPDATE call_sessions SET status = ?, 
            ended_at = CASE WHEN ? = 'ended' THEN NOW() ELSE ended_at END
            WHERE call_id = ?
        ");

        $stmt->execute([$status, $status, $callId]);
    }
}
