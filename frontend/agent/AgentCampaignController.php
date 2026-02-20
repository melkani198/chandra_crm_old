<?php
session_start();
require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
    UPDATE agent_status
    SET campaign_id = ?
    WHERE agent_id = ?
");

$stmt->execute([
    $data['campaign_id'],
    $_SESSION['user_id']
]);

$_SESSION['campaign_id'] = $data['campaign_id'];

echo json_encode(["status"=>"joined"]);