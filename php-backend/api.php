<?php
require_once "../auth_check.php";

if ($_SESSION['role'] !== 'admin') {
    die("Access denied");
}

$token = $_SESSION['token'];
$user  = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Vista CRM - Admin</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family: 'Segoe UI', sans-serif;
    background:linear-gradient(135deg,#0f172a,#1e1b4b);
    color:#fff;
    display:flex;
}

/* ===== SIDEBAR ===== */
.sidebar{
    width:250px;
    background:rgba(0,0,0,0.4);
    backdrop-filter:blur(20px);
    height:100vh;
    padding:20px;
}

.sidebar h2{
    margin-bottom:30px;
    font-size:20px;
}

.sidebar a{
    display:block;
    padding:12px;
    border-radius:10px;
    color:#cbd5e1;
    text-decoration:none;
    margin-bottom:10px;
    transition:.3s;
}

.sidebar a:hover{
    background:#0ea5e9;
    color:#fff;
}

/* ===== MAIN ===== */
.main{
    flex:1;
    padding:30px;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.header h1{
    font-size:22px;
}

.refresh-btn{
    padding:10px 15px;
    background:#0ea5e9;
    border:none;
    border-radius:10px;
    color:#fff;
    cursor:pointer;
}

/* ===== CARDS ===== */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
}

.card{
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(20px);
    padding:20px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.5);
    transition:.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3{
    font-size:14px;
    color:#94a3b8;
}

.card p{
    font-size:28px;
    margin-top:10px;
    font-weight:600;
}

/* ===== TABLE ===== */
.table-container{
    margin-top:40px;
    background:rgba(255,255,255,0.05);
    padding:20px;
    border-radius:15px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:12px;
    text-align:left;
    font-size:14px;
}

th{
    color:#94a3b8;
}

tr:hover{
    background:rgba(255,255,255,0.05);
}
</style>
</head>

<body>

<div class="sidebar">
    <h2><i class="fa-solid fa-phone-volume"></i> Vista CRM</h2>

    <a href="#"><i class="fa-solid fa-chart-line"></i> Live Dashboard</a>
    <a href="#"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
    <a href="#"><i class="fa-solid fa-users"></i> Users</a>
    <a href="#"><i class="fa-solid fa-file"></i> CDR Reports</a>
    <a href="#"><i class="fa-solid fa-gear"></i> Settings</a>
    <a href="../logout.php" style="color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">

<div class="header">
    <h1>Live Monitoring</h1>
    <button class="refresh-btn" onclick="loadStats()">
        <i class="fa-solid fa-rotate"></i> Refresh
    </button>
</div>

<div class="cards">
    <div class="card">
        <h3>Total Agents</h3>
        <p id="total_agents">0</p>
    </div>

    <div class="card">
        <h3>On Call</h3>
        <p id="agents_on_call">0</p>
    </div>

    <div class="card">
        <h3>Idle</h3>
        <p id="agents_idle">0</p>
    </div>

    <div class="card">
        <h3>On Break</h3>
        <p id="agents_on_break">0</p>
    </div>

    <div class="card">
        <h3>Dialed Calls</h3>
        <p id="dialed_calls">0</p>
    </div>

    <div class="card">
        <h3>Connected</h3>
        <p id="connected_calls">0</p>
    </div>
</div>

<div class="table-container">
    <h3>Agent Status</h3>
    <table>
        <thead>
            <tr>
                <th>Agent</th>
                <th>Extension</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="agentTable">
            <tr><td colspan="3">No agents online</td></tr>
        </tbody>
    </table>
</div>

</div>

<script>
const token = "<?php echo $token; ?>";

function loadStats(){
    fetch("/chandra_crm/php-backend/dashboard/stats",{
        headers:{ "Authorization":"Bearer "+token }
    })
    .then(res=>res.json())
    .then(data=>{
        document.getElementById("total_agents").innerText=data.total_agents;
        document.getElementById("agents_on_call").innerText=data.agents_on_call;
        document.getElementById("agents_idle").innerText=data.agents_idle;
        document.getElementById("agents_on_break").innerText=data.agents_on_break;
        document.getElementById("dialed_calls").innerText=data.dialed_calls;
        document.getElementById("connected_calls").innerText=data.connected_calls;
    });

    fetch("/chandra_crm/php-backend/dashboard/agents",{
        headers:{ "Authorization":"Bearer "+token }
    })
    .then(res=>res.json())
    .then(data=>{
        const table=document.getElementById("agentTable");
        table.innerHTML="";
        if(data.length===0){
            table.innerHTML="<tr><td colspan='3'>No agents online</td></tr>";
            return;
        }
        data.forEach(agent=>{
            table.innerHTML+=`
                <tr>
                    <td>${agent.full_name}</td>
                    <td>${agent.extension}</td>
                    <td>${agent.status}</td>
                </tr>`;
        });
    });
}

loadStats();
setInterval(loadStats,5000);
</script>

</body>
</html>
