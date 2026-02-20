<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /chandra_crm/login.php");
    exit;
}