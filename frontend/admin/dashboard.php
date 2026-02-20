<?php include 'layout.php'; ?>

<h1>Live Monitoring</h1>
<div id="stats"></div>

<script>
fetch("../php-backend/api.php/dashboard/stats", {
    headers: {
        "Authorization": "Bearer <?php echo $_SESSION['token']; ?>"
    }
})
.then(res => res.json())
.then(data => {
    document.getElementById("stats").innerHTML =
        "<pre>" + JSON.stringify(data, null, 2) + "</pre>";
});
</script>

</main>
</div>
</body>
</html>
