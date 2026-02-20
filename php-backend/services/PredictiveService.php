<?php

class PredictiveService
{
    public static function runCampaign($campaignId)
    {
        global $pdo;

        // Get pacing
        $stmt = $pdo->prepare("SELECT pacing FROM campaigns WHERE id = ?");
        $stmt->execute([$campaignId]);
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$campaign) return;

        $pacing = floatval($campaign['pacing']);

        // Count READY agents
        $agentsStmt = $pdo->prepare("
            SELECT COUNT(*) as ready_count
            FROM agent_status
            WHERE campaign_id = ?
            AND status = 'ready'
        ");

        $agentsStmt->execute([$campaignId]);
        $readyAgents = $agentsStmt->fetch()['ready_count'];

        if ($readyAgents == 0) return;

        $callsToDial = ceil($readyAgents * $pacing);

        for ($i = 0; $i < $callsToDial; $i++) {

            $contact = DialerService::getNextContact($campaignId, 0);

            if (!$contact) break;

            self::dialPredictive($contact, $campaignId);
        }
    }

    private static function dialPredictive($contact, $campaignId)
    {
        global $pdo;

        $telephony = new TelephonyService();

        $response = $telephony->dial([
            "number" => $contact['phone'],
            "campaign_id" => $campaignId,
            "mode" => "predictive"
        ]);

        $stmt = $pdo->prepare("
            CALL sp_log_cdr(?, NULL, ?, ?, ?, NULL, 'predictive', 'dialing')
        ");

        $stmt->execute([
            $response['call_id'],
            $campaignId,
            $contact['id'],
            $contact['phone']
        ]);
    }
}
