function loadQueueStats() {
    const campaignId = document.getElementById("campaignSelect").value;

    fetch("../../php-backend/controllers/QueueController.php?campaign_id=" + campaignId)
        .then(res => res.json())
        .then(data => {

            document.getElementById("queuedCount").innerText = data.stats.queued;
            document.getElementById("dialingCount").innerText = data.stats.dialing;
            document.getElementById("answeredCount").innerText = data.stats.answered;
            document.getElementById("completedCount").innerText = data.stats.completed;
            document.getElementById("activeCalls").innerText = data.stats.active_calls;
            document.getElementById("readyAgents").innerText = data.stats.ready_agents;

            let html = "";
            data.queue.forEach(row => {
                html += `
                    <tr>
                        <td>${row.contact_id}</td>
                        <td>${row.status}</td>
                        <td>${row.attempt_count}</td>
                        <td>${row.scheduled_at}</td>
                    </tr>
                `;
            });

            document.getElementById("queueTable").innerHTML = html;
        });
}

setInterval(loadQueueStats, 3000);
