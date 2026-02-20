<?php
session_start();
require '../../php-backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit;
}

$agentId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enterprise Agent Console</title>
    <link rel="stylesheet" href="agent.css">
</head>
<body>

<div class="agent-container">

    <div class="top-bar">
        <div class="left">
            <span>Agent Console</span>
        </div>

        <div class="center">
            <span id="statusBadge" class="badge offline">OFFLINE</span>
            <span id="callTimer">00:00</span>
        </div>

        <div class="right">
            <button onclick="setReady()">Ready</button>
            <button onclick="setBreak()">Break</button>
            <button onclick="hangup()">Hangup</button>
        </div>
    </div>

    <div class="main-layout">

        <div class="customer-panel">
            <h3>Customer</h3>
            <div id="customerDetails"></div>
        </div>

        <div class="script-panel">
            <h3>Script</h3>
            <div id="scriptArea">Waiting for call...</div>
        </div>

        <div class="disposition-panel">
            <h3>Disposition</h3>

            <select id="mainDisposition"></select>
            <select id="subDisposition"></select>

            <textarea id="callNotes" placeholder="Enter notes"></textarea>

            <input type="datetime-local" id="callbackTime" style="display:none;">

            <button onclick="saveDisposition()">Save</button>
        </div>

    </div>

</div>

<script src="agent.js"></script>
</body>
</html>