<?php
/**
 * Vista CRM - Settings Controller
 */

require_once __DIR__ . '/../models/Database.php';

class SettingsController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $settings = $this->db->fetchAll("SELECT setting_key, setting_value, description FROM settings");
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }

        return ['data' => [
            'id' => 1,
            'pbx_base_url' => $result['pbx_base_url'] ?? '',
            'pbx_api_key' => $result['pbx_api_key'] ?? '',
            'pbx_port' => $result['pbx_port'] ?? '3200',
            'company_name' => $result['company_name'] ?? 'Vista CRM',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'code' => 200];
    }

    public function update($data, $currentUser) {
        if ($currentUser['role'] !== 'admin') {
            return ['error' => 'Admin access required', 'code' => 403];
        }

        $allowedKeys = ['pbx_base_url', 'pbx_api_key', 'pbx_port', 'company_name'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys) && $value !== null) {
                $this->db->query(
                    "UPDATE settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?",
                    [$value, $currentUser['id'], $key]
                );
            }
        }

        return $this->getAll();
    }

    public function testConnection() {
        $baseUrl = $this->db->getSetting('pbx_base_url');
        
        if (empty($baseUrl)) {
            return ['error' => 'PBX URL not configured', 'code' => 400];
        }

        $apiKey = $this->db->getSetting('pbx_api_key');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rtrim($baseUrl, '/') . '/api/meta/init');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        if ($apiKey) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic {$apiKey}"]);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => "Connection failed: {$error}", 'code' => 503];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['data' => ['status' => 'success', 'response' => json_decode($response, true)], 'code' => 200];
        }

        return ['error' => "PBX returned HTTP {$httpCode}", 'code' => 503];
    }
}
?>
