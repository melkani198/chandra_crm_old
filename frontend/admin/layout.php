<?php
require_once "../auth_check.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Chandra CRM</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">Chandra CRM</div>

        <ul>
            <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Live Dashboard</a></li>
            <li><a href="campaigns.php"><i class="fas fa-bullhorn"></i> Campaigns</a></li>
            <li><a href="process_master.php"><i class="fas fa-cogs"></i> Process Master</a></li>
            <li><a href="contacts.php"><i class="fas fa-address-book"></i> Contacts</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="cdr.php"><i class="fas fa-file-alt"></i> CDR Reports</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="../logout.php" style="color:red;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">

