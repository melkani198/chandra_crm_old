<?php require '../layout.php'; ?>

<link rel="stylesheet" href="queue-monitor.css">

<div class="queue-wrapper">

    <h2>Predictive Queue Monitor</h2>

    <select id="campaignSelect"></select>

    <div class="stats-grid">
        <div class="card">Queued: <span id="queuedCount">0</span></div>
        <div class="card">Dialing: <span id="dialingCount">0</span></div>
        <div class="card">Answered: <span id="answeredCount">0</span></div>
        <div class="card">Completed: <span id="completedCount">0</span></div>
        <div class="card">Active Calls: <span id="activeCalls">0</span></div>
        <div class="card">Ready Agents: <span id="readyAgents">0</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Contact ID</th>
                <th>Status</th>
                <th>Attempts</th>
                <th>Scheduled</th>
            </tr>
        </thead>
        <tbody id="queueTable"></tbody>
    </table>

</div>

<script src="queue-monitor.js"></script>
