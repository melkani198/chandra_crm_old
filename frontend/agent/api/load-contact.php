<?php
require '../../../php-backend/config/database.php';

$phone = $_GET['phone'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM contacts WHERE phone = ? LIMIT 1");
$stmt->execute([$phone]);

echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
