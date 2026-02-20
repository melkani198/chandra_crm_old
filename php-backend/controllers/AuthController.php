<?php
/**
 * Vista CRM - Authentication Controller
 */

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers/JWT.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($data) {

        $required = ['username', 'email', 'password', 'full_name'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['error' => "Field '{$field}' is required", 'code' => 400];
            }
        }

        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$data['username'], $data['email']]
        );

        if ($existing) {
            return ['error' => 'Username or email already exists', 'code' => 400];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $userId = $this->db->insert('users', [
            'uuid' => $this->generateUUID(),
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'full_name' => $data['full_name'],
            'role' => $data['role'] ?? 'agent',
            'extension' => $data['extension'] ?? null,
            'phone' => $data['phone'] ?? null,
            'is_active' => 1
        ]);

        $user = $this->db->fetch(
            "SELECT id, uuid, username, email, full_name, role, extension, phone, is_active, created_at FROM users WHERE id = ?",
            [$userId]
        );

        return ['data' => $user, 'code' => 201];
    }

    public function login($data) {

        if (empty($data['username']) || empty($data['password'])) {
            return ['error' => 'Username and password are required', 'code' => 400];
        }

        $user = $this->db->fetch(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$data['username']]
        );

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return ['error' => 'Invalid credentials', 'code' => 401];
        }

        $this->db->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $user['id']]
        );

        $token = JWT::encode([
            'user_id' => $user['id'],
            'uuid' => $user['uuid'],
            'role' => $user['role']
        ]);

        unset($user['password']);

        return [
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $user
            ],
            'code' => 200
        ];
    }

    public function me($userId) {

        $user = $this->db->fetch(
            "SELECT id, uuid, username, email, full_name, role, extension, phone, is_active, last_login, created_at FROM users WHERE id = ?",
            [$userId]
        );

        if (!$user) {
            return ['error' => 'User not found', 'code' => 404];
        }

        return ['data' => $user, 'code' => 200];
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
