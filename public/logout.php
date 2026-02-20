<?php
session_start();

$_SESSION = [];
session_destroy();

header("Location: /chandra_crm/login.php");
exit;