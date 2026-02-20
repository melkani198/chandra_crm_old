<?php

class TelephonyService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = "http://192.168.1.116:3200";
        $this->apiKey = "YWRtaW46YWRtaW5AMTIz";
    }

    private function request($endpoint, $data = [])
    {
        $ch = curl_init($this->baseUrl . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Basic {$this->apiKey}"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function agentAction($data)
    {
        return $this->request("/api/agent", $data);
    }

    public function dial($data)
    {
        return $this->request("/api/dial", $data);
    }

    public function hangup($data)
    {
        return $this->request("/api/agent", array_merge($data, ["action" => "hangup"]));
    }
}
