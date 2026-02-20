<?php

class PBXClient
{
    private $socket;
    private $host;
    private $port;
    private $username;
    private $secret;

    public function __construct()
    {
        $this->host = '127.0.0.1';
        $this->port = 5038;
        $this->username = 'chandu';
        $this->secret = 'Csm@1989';
    }

    public function connect()
    {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);

        if (!$this->socket) {
            throw new Exception("AMI Connection failed: $errstr ($errno)");
        }

        $this->login();
    }

    private function login()
    {
        $this->sendAction([
            'Action' => 'Login',
            'Username' => $this->username,
            'Secret' => $this->secret
        ]);
    }

    public function sendAction($data)
    {
        foreach ($data as $key => $value) {
            fputs($this->socket, "$key: $value\r\n");
        }
        fputs($this->socket, "\r\n");
    }

    public function read()
    {
        $response = '';
        while (!feof($this->socket)) {
            $line = fgets($this->socket, 4096);
            if (trim($line) === '') break;
            $response .= $line;
        }
        return $this->parseResponse($response);
    }

    private function parseResponse($raw)
    {
        $data = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (!$line) continue;
            $parts = explode(': ', $line, 2);
            if (count($parts) == 2) {
                $data[$parts[0]] = $parts[1];
            }
        }
        return $data;
    }
}
