<?php
require_once __DIR__ . "/auth_check.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Chandra CRM</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}

body{
font-family:'Inter',sans-serif;
background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);
color:#fff;
min-height:100vh;
}

.layout{display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{
width:260px;
background:rgba(15,23,42,0.7);
backdrop-filter:blur(20px);
border-right:1px solid rgba(255,255,255,0.05);
padding:20px 0;
position:fixed;
height:100vh;
}

.sidebar .logo{
display:flex;
align-items:center;
gap:12px;
padding:0 20px 20px;
border-bottom:1px solid rgba(255,255,255,0.05);
margin-bottom:20px;
font-weight:700;
font-size:18px;
}

.sidebar ul{list-style:none;padding:0 10px}
.sidebar li{margin-bottom:5px}

.sidebar a{
display:flex;
align-items:center;
gap:12px;
padding:12px 15px;
color:rgba(255,255,255,0.6);
text-decoration:none;
border-radius:10px;
transition:.2s;
font-size:14px;
}

.sidebar a:hover,
.sidebar a.active{
background:rgba(6,182,212,0.1);
color:#06b6d4;
}

/* MAIN */
.main-content{
flex:1;
margin-left:260px;
padding:30px;
}

/* HEADER */
.page-header h1{
font-size:24px;
font-weight:700;
margin-bottom:5px;
}

.page-header p{
color:rgba(255,255,255,0.5);
font-size:14px;
margin-bottom:25px;
}

/* CARDS */
.card{
background:rgba(15,23,42,0.4);
backdrop-filter:blur(20px);
border:1px solid rgba(255,255,255,0.1);
border-radius:15px;
padding:20px;
margin-bottom:20px;
}

.stats-grid{
display:grid;
grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
gap:20px;
margin-bottom:30px;
}

.stat-card{
position:relative;
background:rgba(15,23,42,0.45);
backdrop-filter:blur(20px);
border:1px solid rgba(255,255,255,0.08);
border-radius:16px;
padding:22px;
transition:all 0.25s ease;
overflow:hidden;
}

.stat-card:hover{
transform:translateY(-4px);
border-color:rgba(255,255,255,0.18);
box-shadow:0 15px 35px rgba(0,0,0,0.35);
}

.stat-card .label{
color:rgba(255,255,255,0.55);
font-size:13px;
margin-bottom:10px;
}

.stat-card .value{
font-size:30px;
font-weight:700;
letter-spacing:1px;
}

.stat-card .icon{
position:absolute;
top:18px;
right:18px;
width:48px;
height:48px;
border-radius:14px;
display:flex;
align-items:center;
justify-content:center;
transition:all 0.25s ease;
}

.stat-card:hover .icon{
transform:scale(1.08);
}

.stat-card .icon i{
color:#fff;
font-size:18px;
}
.bg-blue {background:linear-gradient(135deg,#3b82f6,#2563eb);}
.bg-green {background:linear-gradient(135deg,#10b981,#059669);}
.bg-yellow {background:linear-gradient(135deg,#f59e0b,#d97706);}
.bg-red {background:linear-gradient(135deg,#ef4444,#dc2626);}
.bg-purple {background:linear-gradient(135deg,#8b5cf6,#7c3aed);}
.bg-cyan {background:linear-gradient(135deg,#06b6d4,#0891b2);}
.bg-slate {background:linear-gradient(135deg,#64748b,#475569);}

.stat-card .icon{
width:45px;
height:45px;
border-radius:12px;
display:flex;
align-items:center;
justify-content:center;
float:right;
margin-top:-5px;
}

.stat-card .icon i{
color:#fff;
font-size:18px;
}
</style>
</head>

<body>
<div class="layout">

<aside class="sidebar">
<div class="logo">
<i class="fas fa-phone-volume"></i>
Chandra CRM
</div>

<ul>
<li><a href="/chandra_crm/frontend/admin/dashboard.php" class="active"><i class="fas fa-chart-line"></i> Live Dashboard</a></li>
<li><a href="#"><i class="fas fa-bullhorn"></i> Campaigns</a></li>
<li><a href="#"><i class="fas fa-cogs"></i> Process Master</a></li>
<li><a href="#"><i class="fas fa-address-book"></i> Contacts</a></li>
<li><a href="#"><i class="fas fa-users"></i> Users</a></li>
<li><a href="#"><i class="fas fa-file-alt"></i> CDR Reports</a></li>
<li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
<li><a href="/chandra_crm/logout.php" style="color:#f87171"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
</aside>

<main class="main-content">