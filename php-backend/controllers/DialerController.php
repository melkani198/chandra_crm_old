<?php
session_start();

require '../config/database.php';
require '../services/DialerService.php';
require '../services/TelephonyService.php';

$agentId = $_SESSION['user_id'];
$campaignId = $_SESSION['campaign_id']; // must be set on login

$contact = DialerService::getNextContact($campaignId, $agentId);

if (!$contact) {
    echo json_encode(["status" => "no_contacts"]);
    exit;
}

$telephony = new TelephonyService();

$response = $telephony->dial([
    "exten" => $_SESSION['extension'],
    "number" => $contact['phone'],
    "campaign_id" => $campaignId
]);

// Log CDR start
$stmt = $pdo->prepare("
    CALL sp_log_cdr(?, ?, ?, ?, ?, ?, 'progressive', 'dialing')
");

$stmt->execute([
    $response['call_id'],
    $agentId,
    $campaignId,
    $contact['id'],
    $contact['phone'],
    $_SESSION['extension']
]);

echo json_encode([
    "status" => "dialing",
    "contact" => $contact,
    "call_id" => $response['call_id']
]);
