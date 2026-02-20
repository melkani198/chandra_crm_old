<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: /chandra_crm/login.php");
    exit;
}

include __DIR__ . '/../layout.php';
?>

<h1>Live Monitoring</h1>

<div id="stats">Loading...</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    fetch("/chandra_crm/php-backend/api.php/dashboard/stats", {
        headers: {
            "Authorization": "Bearer <?php echo $_SESSION['token']; ?>"
        }
    })
    .then(res => {
        if (!res.ok) {
            throw new Error("API error");
        }
        return res.json();
    })
    .then(data => {
        document.getElementById("stats").innerHTML =
            "<pre>" + JSON.stringify(data, null, 2) + "</pre>";
    })
    .catch(err => {
        document.getElementById("stats").innerHTML =
            "<div style='color:red;'>Failed to load dashboard data</div>";
        console.error(err);
    });

});
</script>

</main>
</div>
</body>
</html>