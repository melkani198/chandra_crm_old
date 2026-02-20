<?php

class DialerService
{
    public static function getNextContact($campaignId, $agentId)
    {
        global $pdo;

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT * FROM contacts
            WHERE campaign_id = ?
            AND status = 'new'
            AND is_active = 1
            AND is_locked = 0
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([$campaignId]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$contact) {
            $pdo->commit();
            return null;
        }

        $lock = $pdo->prepare("
            UPDATE contacts
            SET is_locked = 1,
                locked_by = ?,
                locked_at = NOW()
            WHERE id = ?
        ");

        $lock->execute([$agentId, $contact['id']]);

        $pdo->commit();

        return $contact;
    }
}
