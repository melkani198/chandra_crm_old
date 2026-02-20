<?php
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: /chandra_crm/login.php");
    exit;
}

function apiCall($endpoint, $method = "GET", $data = null)
{
    $url = "http://localhost/chandra_crm/php-backend/" . $endpoint;

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $_SESSION['token']
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
