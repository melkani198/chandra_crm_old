<?php
/**
 * Vista CRM - User Controller
 */

require_once __DIR__ . '/../models/Database.php';

class UserController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = 'role = ?';
            $params[] = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $where[] = 'is_active = ?';
            $params[] = $filters['is_active'];
        }

        $sql = "SELECT id, uuid, username, email, full_name, role, extension, phone, is_active, last_login, created_at 
                FROM users WHERE " . implode(' AND ', $where) . " ORDER BY full_name";

        return ['data' => $this->db->fetchAll($sql, $params), 'code' => 200];
    }

    public function getById($id) {
        $user = $this->db->fetch(
            "SELECT id, uuid, username, email, full_name, role, extension, phone, is_active, last_login, created_at 
             FROM users WHERE id = ?",
            [$id]
        );

        if (!$user) {
            return ['error' => 'User not found', 'code' => 404];
        }

        return ['data' => $user, 'code' => 200];
    }

    public function update($id, $data, $currentUser) {
        // Only admin can update other users
        if ($currentUser['role'] !== 'admin' && $currentUser['id'] != $id) {
            return ['error' => 'Access denied', 'code' => 403];
        }

        // Don't allow password update through this endpoint
        unset($data['password']);
        
        $allowed = ['email', 'full_name', 'role', 'extension', 'phone', 'is_active'];
        $updateData = array_intersect_key($data, array_flip($allowed));

        if (empty($updateData)) {
            return ['error' => 'No valid fields to update', 'code' => 400];
        }

        $this->db->update('users', $updateData, 'id = :id', ['id' => $id]);

        return $this->getById($id);
    }

    public function delete($id, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $result = $this->db->delete('users', 'id = ?', [$id]);

        if ($result === 0) {
            return ['error' => 'User not found', 'code' => 404];
        }

        return ['data' => ['message' => 'User deleted successfully'], 'code' => 200];
    }
}
?>
