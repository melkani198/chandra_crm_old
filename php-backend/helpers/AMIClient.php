<?php

class AMIClient
{
    private $socket;
    private $host = '127.0.0.1';
    private $port = 5038;
    private $username = 'chandu';
    private $secret = 'Csm@1989';

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
        $this->send([
            'Action' => 'Login',
            'Username' => $this->username,
            'Secret' => $this->secret
        ]);
    }

    public function send($data)
    {
        foreach ($data as $key => $value) {
            fwrite($this->socket, "$key: $value\r\n");
        }
        fwrite($this->socket, "\r\n");
    }

    public function read()
    {
        $response = '';
        while (!feof($this->socket)) {
            $line = fgets($this->socket, 4096);
            if (trim($line) === '') break;
            $response .= $line;
        }
        return $this->parse($response);
    }

    private function parse($raw)
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

