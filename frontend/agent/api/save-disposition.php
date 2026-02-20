<?php
session_start();
require '../../../php-backend/config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$agentId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    CALL sp_update_cdr_end(?, 'answered', ?, ?)
");

$stmt->execute([
    $data['call_id'],
    $data['disposition'],
    $data['notes']
]);

// Update contact
$update = $pdo->prepare("
    UPDATE contacts 
    SET disposition_id = ?, status = 'connected'
    WHERE id = ?
");

$update->execute([
    $data['disposition'],
    $data['contact_id']
]);

echo json_encode(["status" => "saved"]);
