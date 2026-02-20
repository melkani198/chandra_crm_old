<?php include 'layout.php'; ?>

<h1>Campaign Management</h1>
<div id="campaignList"></div>

<script>
fetch("../php-backend/api.php/campaigns", {
    headers: {
        "Authorization": "Bearer <?php echo $_SESSION['token']; ?>"
    }
})
.then(res => res.json())
.then(data => {
    let html = "<table>";
    html += "<tr><th>ID</th><th>Name</th><th>Mode</th><th>Status</th></tr>";
    data.forEach(c => {
        html += `<tr>
                    <td>${c.id}</td>
                    <td>${c.name}</td>
                    <td>${c.mode}</td>
                    <td>${c.status}</td>
                 </tr>`;
    });
    html += "</table>";
    document.getElementById("campaignList").innerHTML = html;
});
</script>

</main>
</div>
</body>
</html>
