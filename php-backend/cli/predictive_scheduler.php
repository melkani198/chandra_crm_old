<?php
require '../config/database.php';
require '../services/DialerService.php';
require '../services/TelephonyService.php';
require '../services/PredictiveService.php';

while (true) {

    $campaigns = $pdo->query("
        SELECT id FROM campaigns 
        WHERE campaign_type = 'predictive'
        AND status = 'active'
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($campaigns as $campaign) {
        PredictiveService::runCampaign($campaign['id']);
    }

    sleep(2);
}
