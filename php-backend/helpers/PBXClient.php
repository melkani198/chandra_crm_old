
<?php
/**
 * Vista CRM - PBX API Client
 */

class PBXClient {
    private $baseUrl;
    private $apiKey;
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    private function loadConfig() {
        $this->baseUrl = $this->db->getSetting('pbx_base_url');
        $this->apiKey = $this->db->getSetting('pbx_api_key');
    }

    public function isConfigured() {
        return !empty($this->baseUrl);
    }

    public function request($endpoint, $method = 'GET', $data = null) {
        if (!$this->isConfigured()) {
            return ['error' => 'PBX not configured'];
        }

        $url = rtrim($this->baseUrl, '/') . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $headers = ['Content-Type: application/json'];
        if ($this->apiKey) {
            $headers[] = "Authorization: Basic {$this->apiKey}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'http_code' => $httpCode];
        }

        return json_decode($response, true) ?: ['raw_response' => $response];
    }

    // Agent Actions
    public function agentLogin($extension, $acd = null) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'login',
            'exten' => $extension,
            'acd' => $acd,
            'uniqueid' => uniqid()
        ]);
    }

    public function agentLogout($extension) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'logout',
            'exten' => $extension,
            'uniqueid' => uniqid()
        ]);
    }

    public function agentReady($extension, $acd = null) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'ready',
            'exten' => $extension,
            'acd' => $acd,
            'uniqueid' => uniqid()
        ]);
    }

    public function agentBreak($extension, $breakType) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'break',
            'exten' => $extension,
            'breaktype' => $breakType,
            'uniqueid' => uniqid()
        ]);
    }

    public function agentManualOn($extension) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'manual_on',
            'exten' => $extension,
            'uniqueid' => uniqid()
        ]);
    }

    public function agentManualOff($extension) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'manual_off',
            'exten' => $extension,
            'uniqueid' => uniqid()
        ]);
    }

    // Dialing
    public function dial($mode, $phone, $extension, $callerId = null, $campaign = null) {
        $data = [
            'mode' => $mode, // progressive, predictive, ivrblast
            'phone' => $phone,
            'exten' => $extension,
            'callerid' => $callerId
        ];

        if ($campaign) {
            $data['campaign'] = $campaign;
        }

        return $this->request('/api/dial', 'POST', $data);
    }

    public function hangup($extension) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'hangup',
            'exten' => $extension,
            'uniqueid' => uniqid()
        ]);
    }

    public function transfer($extension, $targetExtension) {
        return $this->request('/api/agent', 'POST', [
            'action' => 'transfer',
            'exten' => $extension,
            'targetexten' => $targetExtension,
            'uniqueid' => uniqid()
        ]);
    }

    // Supervisor Actions
    public function barge($supervisorExt, $agentExt) {
        return $this->request('/api/barge', 'POST', [
            'exten' => $supervisorExt,
            'agentexten' => $agentExt
        ]);
    }

    public function whisper($supervisorExt, $agentExt) {
        return $this->request('/api/whisper', 'POST', [
            'exten' => $supervisorExt,
            'agentexten' => $agentExt
        ]);
    }

    // Meta/Status
    public function getMetaInit() {
        return $this->request('/api/meta/init', 'GET');
    }

    public function getLiveAgents() {
        return $this->request('/api/agent/list', 'GET');
    }
}
?>
