<?php
require '../config/database.php';

$campaignId = $_GET['campaign_id'];

$stats = [];

$statuses = ['queued','dialing','answered','completed'];

foreach ($statuses as $status) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM contact_queue
        WHERE campaign_id = ?
        AND status = ?
    ");
    $stmt->execute([$campaignId, $status]);
    $stats[$status] = $stmt->fetchColumn();
}

$activeCalls = $pdo->prepare("
    SELECT COUNT(*) FROM cdr
    WHERE campaign_id = ?
    AND call_status IN ('dialing','ringing','answered')
");
$activeCalls->execute([$campaignId]);

$readyAgents = $pdo->prepare("
    SELECT COUNT(*) FROM agent_status
    WHERE campaign_id = ?
    AND status = 'ready'
");
$readyAgents->execute([$campaignId]);

$queue = $pdo->prepare("
    SELECT * FROM contact_queue
    WHERE campaign_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$queue->execute([$campaignId]);

echo json_encode([
    "stats" => [
        "queued" => $stats['queued'],
        "dialing" => $stats['dialing'],
        "answered" => $stats['answered'],
        "completed" => $stats['completed'],
        "active_calls" => $activeCalls->fetchColumn(),
        "ready_agents" => $readyAgents->fetchColumn()
    ],
    "queue" => $queue->fetchAll(PDO::FETCH_ASSOC)
]);
