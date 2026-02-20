<?php
if ($_SESSION['role'] !== $requiredRole) {
    header("Location: /chandra_crm/public/index.php");
    exit;
}
