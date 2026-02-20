<?php
require_once '../services/ProcessService.php';

$data = json_decode(file_get_contents("php://input"), true);

switch ($_GET['action'] ?? null) {

    case "create":
        $id = ProcessService::createProcess($data);
        echo json_encode(["process_id" => $id]);
        break;

    case "add_field":
        ProcessService::addField($data['process_id'], $data['field']);
        echo json_encode(["status" => "field_added"]);
        break;

    case "attach_disposition":
        ProcessService::attachDisposition($data['process_id'], $data['disposition_id']);
        echo json_encode(["status" => "attached"]);
        break;

    case "structure":
        $structure = ProcessService::getProcessFullStructure($_GET['process_id']);
        echo json_encode($structure);
        break;
}