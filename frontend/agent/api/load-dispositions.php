<?php
require '../../../php-backend/config/database.php';

$stmt = $pdo->query("SELECT * FROM dispositions WHERE is_active = 1 ORDER BY sort_order");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
