<?php 
include __DIR__ . '/../../layout.php'; 
?>

<div class="page-header">
    <h1>Live Monitoring</h1>
    <p>Real-time agent and call statistics</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="icon bg-blue"><i class="fas fa-users"></i></div>
        <div class="label">Total Agents</div>
        <div class="value" id="total_agents">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-yellow"><i class="fas fa-coffee"></i></div>
        <div class="label">On Break</div>
        <div class="value" id="agents_on_break">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-slate"><i class="fas fa-clock"></i></div>
        <div class="label">Idle</div>
        <div class="value" id="agents_idle">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-red"><i class="fas fa-phone"></i></div>
        <div class="label">Manual On</div>
        <div class="value" id="agents_manual_on">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-green"><i class="fas fa-phone-volume"></i></div>
        <div class="label">On Call</div>
        <div class="value" id="agents_on_call">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-purple"><i class="fas fa-hourglass-half"></i></div>
        <div class="label">Wrap Up</div>
        <div class="value" id="agents_wrap_up">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-cyan"><i class="fas fa-phone-alt"></i></div>
        <div class="label">Dialed Calls</div>
        <div class="value" id="dialed_calls">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-green"><i class="fas fa-check-circle"></i></div>
        <div class="label">Connected</div>
        <div class="value" id="connected_calls">0</div>
    </div>

    <div class="stat-card">
        <div class="icon bg-cyan"><i class="fas fa-percentage"></i></div>
        <div class="label">Connectivity %</div>
        <div class="value" id="connectivity_percentage">0%</div>
    </div>

</div>

<!-- Agent Table -->
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <h3><i class="fas fa-user-clock"></i> Agent Status</h3>
        <button onclick="loadDashboard()" 
            style="background:rgba(255,255,255,0.1);border:none;padding:8px 15px;border-radius:8px;color:#fff;cursor:pointer;">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <div id="agentTableWrapper" style="padding:10px;">
        <div style="text-align:center;padding:40px;color:rgba(255,255,255,0.5);">
            Loading...
        </div>
    </div>
</div>

</main>
</div>

<script>
function loadDashboard(){

    // ===== LOAD STATS =====
    fetch("/chandra_crm/php-backend/api.php/dashboard/stats", {
        credentials: "include"
    })
    .then(res => res.json())
    .then(data => {

        if (!data || data.detail) {
            console.log("API Error:", data);
            return;
        }

        document.getElementById("total_agents").innerText = data.total_agents ?? 0;
        document.getElementById("agents_on_break").innerText = data.agents_on_break ?? 0;
        document.getElementById("agents_idle").innerText = data.agents_idle ?? 0;
        document.getElementById("agents_manual_on").innerText = data.agents_manual_on ?? 0;
        document.getElementById("agents_on_call").innerText = data.agents_on_call ?? 0;
        document.getElementById("agents_wrap_up").innerText = data.agents_wrap_up ?? 0;
        document.getElementById("dialed_calls").innerText = data.dialed_calls ?? 0;
        document.getElementById("connected_calls").innerText = data.connected_calls ?? 0;
        document.getElementById("connectivity_percentage").innerText = (data.connectivity_percentage ?? 0) + "%";
    })
    .catch(err => console.log("Stats Fetch error:", err));


    // ===== LOAD AGENTS =====
    fetch("/chandra_crm/php-backend/api.php/dashboard/agents", {
        credentials: "include"
    })
    .then(res => res.json())
    .then(data => {

        const wrapper = document.getElementById("agentTableWrapper");

        if (!data || data.length === 0) {
            wrapper.innerHTML = "<div style='text-align:center;padding:30px;color:rgba(255,255,255,0.5);'>No agents online</div>";
            return;
        }

        let html = `
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <th style="text-align:left;padding:8px;">Agent</th>
                    <th style="text-align:left;padding:8px;">Extension</th>
                    <th style="text-align:left;padding:8px;">Status</th>
                </tr>
        `;

        data.forEach(agent => {
            html += `
                <tr>
                    <td style="padding:8px;">${agent.agent_name}</td>
                    <td style="padding:8px;">${agent.extension}</td>
                    <td style="padding:8px;">${agent.status}</td>
                </tr>
            `;
        });

        html += "</table>";

        wrapper.innerHTML = html;
    })
    .catch(err => console.log("Agents Fetch error:", err));
}

loadDashboard();
setInterval(loadDashboard, 5000);
</script>

</body>
</html>