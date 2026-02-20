<?php
session_start();
require '../../../php-backend/config/database.php';
require '../../../php-backend/services/TelephonyService.php';
require '../../../php-backend/services/AgentStateService.php';

$data = json_decode(file_get_contents("php://input"), true);

$agentId = $_SESSION['user_id'] ?? null;
if (!$agentId) {
    http_response_code(401);
    exit;
}

$telephony = new TelephonyService();

switch ($data['action']) {

    case "ready":
        $telephony->agentAction([
            "action" => "ready",
            "exten" => $_SESSION['extension']
        ]);

        AgentStateService::updateStatus($agentId, "ready", $_SESSION['extension']);
        echo json_encode(["status" => "ready"]);
        break;

    case "break":
        $telephony->agentAction([
            "action" => "break",
            "exten" => $_SESSION['extension']
        ]);

        AgentStateService::updateStatus($agentId, "break", $_SESSION['extension']);
        echo json_encode(["status" => "break"]);
        break;

    case "hangup":
        $telephony->hangup([
            "exten" => $_SESSION['extension']
        ]);

        echo json_encode(["status" => "hangup_sent"]);
        break;
}
