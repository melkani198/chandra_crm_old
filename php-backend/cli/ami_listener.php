<?php

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers/AMIClient.php';

echo "Starting AMI Listener...\n";

$pbx = new AMIClient();
$pbx->connect();

$db = Database::getInstance();

while (true) {

    $event = $pbx->read();

    if (!isset($event['Event'])) {
        continue;
    }

    switch ($event['Event']) {

        case 'DialBegin':
            handleDialBegin($event, $db);
            break;

        case 'BridgeEnter':
            handleBridgeEnter($event, $db);
            break;

        case 'Hangup':
            handleHangup($event, $db);
            break;
    }
}

function handleDialBegin($event, $db)
{
    $agentExt = extractExtension($event['Channel'] ?? '');
    if (!$agentExt) return;

    $agent = getAgentByExtension($agentExt, $db);
    if (!$agent) return;

    $db->query("CALL sp_update_agent_status(?, 'ringing', ?, NULL, NULL, NULL)", [
        $agent['id'],
        $agentExt
    ]);
}

function handleBridgeEnter($event, $db)
{
    $agentExt = extractExtension($event['Channel'] ?? '');
    if (!$agentExt) return;

    $agent = getAgentByExtension($agentExt, $db);
    if (!$agent) return;

    $db->query("CALL sp_update_agent_status(?, 'on_call', ?, NULL, NULL, NULL)", [
        $agent['id'],
        $agentExt
    ]);
}

function handleHangup($event, $db)
{
    $agentExt = extractExtension($event['Channel'] ?? '');
    if (!$agentExt) return;

    $agent = getAgentByExtension($agentExt, $db);
    if (!$agent) return;

    $db->query("CALL sp_update_agent_status(?, 'wrap_up', ?, NULL, NULL, NULL)", [
        $agent['id'],
        $agentExt
    ]);
}

function extractExtension($channel)
{
    if (preg_match('/SIP\/(\d+)/', $channel, $matches)) {
        return $matches[1];
    }
    return null;
}

function getAgentByExtension($ext, $db)
{
    return $db->fetch("SELECT id FROM users WHERE extension = ?", [$ext]);
}
