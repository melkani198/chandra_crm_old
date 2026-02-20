<?php

class AgentStateService
{
    public static function updateStatus($agentId, $status, $extension = null)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            CALL sp_update_agent_status(?, ?, ?, NULL, NULL, NULL)
        ");
        $stmt->execute([$agentId, $status, $extension]);
    }
}
