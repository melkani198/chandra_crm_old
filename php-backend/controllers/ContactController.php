<?php
/**
 * Vista CRM - Contact Controller
 */

require_once __DIR__ . '/../models/Database.php';

class ContactController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['campaign_id'])) {
            $where[] = 'c.campaign_id = ?';
            $params[] = $filters['campaign_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(c.first_name LIKE ? OR c.last_name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $limit = $filters['limit'] ?? 100;
        $offset = $filters['offset'] ?? 0;

        $sql = "SELECT c.*, camp.name as campaign_name, d.name as disposition_name 
                FROM contacts c 
                LEFT JOIN campaigns camp ON camp.id = c.campaign_id 
                LEFT JOIN dispositions d ON d.id = c.disposition_id
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY c.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        return ['data' => $this->db->fetchAll($sql, $params), 'code' => 200];
    }

    public function getById($id) {
        $contact = $this->db->fetch(
            "SELECT c.*, camp.name as campaign_name, d.name as disposition_name 
             FROM contacts c 
             LEFT JOIN campaigns camp ON camp.id = c.campaign_id 
             LEFT JOIN dispositions d ON d.id = c.disposition_id
             WHERE c.id = ?",
            [$id]
        );

        if (!$contact) {
            return ['error' => 'Contact not found', 'code' => 404];
        }

        return ['data' => $contact, 'code' => 200];
    }

    public function create($data) {
        if (empty($data['first_name']) || empty($data['phone'])) {
            return ['error' => 'First name and phone are required', 'code' => 400];
        }

        $contactId = $this->db->insert('contacts', [
            'uuid' => $this->generateUUID(),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['phone'],
            'alternate_phone' => $data['alternate_phone'] ?? null,
            'email' => $data['email'] ?? null,
            'company' => $data['company'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'status' => $data['status'] ?? 'new',
            'notes' => $data['notes'] ?? null,
            'custom_fields' => isset($data['custom_fields']) ? json_encode($data['custom_fields']) : null
        ]);

        // Update campaign contact count
        if (!empty($data['campaign_id'])) {
            $this->db->query(
                "UPDATE campaigns SET total_contacts = total_contacts + 1 WHERE id = ?",
                [$data['campaign_id']]
            );
        }

        return $this->getById($contactId);
    }

    public function bulkCreate($contacts) {
        $created = 0;
        foreach ($contacts as $data) {
            if (!empty($data['phone'])) {
                $this->db->insert('contacts', [
                    'uuid' => $this->generateUUID(),
                    'first_name' => $data['first_name'] ?? 'Unknown',
                    'last_name' => $data['last_name'] ?? '',
                    'phone' => $data['phone'],
                    'email' => $data['email'] ?? null,
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'status' => 'new'
                ]);
                $created++;

                if (!empty($data['campaign_id'])) {
                    $this->db->query(
                        "UPDATE campaigns SET total_contacts = total_contacts + 1 WHERE id = ?",
                        [$data['campaign_id']]
                    );
                }
            }
        }

        return ['data' => ['message' => "{$created} contacts created successfully"], 'code' => 201];
    }

    public function update($id, $data) {
        $allowed = ['first_name', 'last_name', 'phone', 'alternate_phone', 'email', 'company',
                    'address', 'city', 'state', 'country', 'campaign_id', 'status', 
                    'disposition_id', 'notes', 'callback_time', 'assigned_agent_id'];
        $updateData = array_intersect_key($data, array_flip($allowed));

        if (empty($updateData)) {
            return ['error' => 'No valid fields to update', 'code' => 400];
        }

        $this->db->update('contacts', $updateData, 'id = :id', ['id' => $id]);

        return $this->getById($id);
    }

    public function delete($id) {
        // Get contact to update campaign count
        $contact = $this->db->fetch("SELECT campaign_id FROM contacts WHERE id = ?", [$id]);

        $result = $this->db->delete('contacts', 'id = ?', [$id]);

        if ($result === 0) {
            return ['error' => 'Contact not found', 'code' => 404];
        }

        // Update campaign contact count
        if ($contact && $contact['campaign_id']) {
            $this->db->query(
                "UPDATE campaigns SET total_contacts = GREATEST(total_contacts - 1, 0) WHERE id = ?",
                [$contact['campaign_id']]
            );
        }

        return ['data' => ['message' => 'Contact deleted successfully'], 'code' => 200];
    }

    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
