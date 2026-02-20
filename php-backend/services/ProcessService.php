<?php

class ProcessService
{
    public static function createProcess($data)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO process_master (uuid, name, description)
            VALUES (UUID(), ?, ?)
        ");

        $stmt->execute([
            $data['name'],
            $data['description']
        ]);

        return $pdo->lastInsertId();
    }

    public static function addField($processId, $field)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO process_fields
            (process_id, field_label, field_key, field_type, is_required, options, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $processId,
            $field['label'],
            $field['key'],
            $field['type'],
            $field['required'],
            json_encode($field['options'] ?? null),
            $field['order']
        ]);
    }

    public static function attachDisposition($processId, $dispositionId)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO process_dispositions (process_id, disposition_id)
            VALUES (?, ?)
        ");

        $stmt->execute([$processId, $dispositionId]);
    }

    public static function addSubDisposition($processDispositionId, $name, $isCallback)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO process_sub_dispositions
            (process_disposition_id, name, is_callback)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $processDispositionId,
            $name,
            $isCallback
        ]);
    }

    public static function getProcessFullStructure($processId)
    {
        global $pdo;

        $fields = $pdo->prepare("SELECT * FROM process_fields WHERE process_id = ?");
        $fields->execute([$processId]);

        $dispositions = $pdo->prepare("
            SELECT pd.id AS process_disposition_id, d.*
            FROM process_dispositions pd
            JOIN dispositions d ON d.id = pd.disposition_id
            WHERE pd.process_id = ?
        ");
        $dispositions->execute([$processId]);

        return [
            "fields" => $fields->fetchAll(PDO::FETCH_ASSOC),
            "dispositions" => $dispositions->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
}