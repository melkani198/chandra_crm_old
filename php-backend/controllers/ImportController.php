<?php
require '../config/database.php';

$action = $_GET['action'] ?? null;

if ($action === "preview") {

    $file = $_FILES['file']['tmp_name'];
    $rows = array_map('str_getcsv', file($file));
    $headers = array_shift($rows);

    $campaignId = 1; // replace dynamically
    $processStmt = $pdo->prepare("
        SELECT pf.* 
        FROM campaigns c
        JOIN process_fields pf ON pf.process_id = c.process_id
        WHERE c.id = ?
    ");
    $processStmt->execute([$campaignId]);

    echo json_encode([
        "headers" => $headers,
        "rows" => $rows,
        "process_fields" => $processStmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
}

if ($action === "import") {

    $data = json_decode(file_get_contents("php://input"), true);

    foreach ($data['data'] as $row) {

        $customFields = [];

        foreach ($data['mapping'] as $csvHeader => $fieldKey) {
            $index = array_search($csvHeader, array_keys($data['mapping']));
            $customFields[$fieldKey] = $row[$index] ?? null;
        }

        $stmt = $pdo->prepare("
            INSERT INTO contacts 
            (uuid, first_name, phone, campaign_id, custom_fields)
            VALUES (UUID(), ?, ?, ?, ?)
        ");

        $stmt->execute([
            $customFields['first_name'] ?? 'NA',
            $customFields['phone'] ?? '',
            $data['campaign_id'],
            json_encode($customFields)
        ]);
    }

    echo json_encode(["status" => "success"]);
}
