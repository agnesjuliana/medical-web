<?php

namespace Backend\Repositories;

use PDO;

class AiScanRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getQuota(int $userId): int
    {
        $stmt = $this->pdo->prepare('
            SELECT scan_count
            FROM m8_ai_scan_quota
            WHERE user_id = ? AND log_date = CURRENT_DATE
        ');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['scan_count'] : 0;
    }

    /**
     * Check if user can scan today without incrementing.
     * Returns false if limit is already reached.
     */
    public function canScanToday(int $userId, int $limit): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT scan_count
            FROM m8_ai_scan_quota
            WHERE user_id = ? AND log_date = CURRENT_DATE
        ');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentCount = $row ? (int) $row['scan_count'] : 0;
        return $currentCount < $limit;
    }

    /**
     * Increment scan quota for today. Should only be called after successful scan.
     */
    public function incrementIfUnderLimit(int $userId, int $limit): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO m8_ai_scan_quota (user_id, log_date, scan_count, created_at, updated_at)
            VALUES (?, CURRENT_DATE, 1, NOW(), NOW())
            ON CONFLICT (user_id, log_date) DO UPDATE
                SET scan_count = m8_ai_scan_quota.scan_count + 1,
                    updated_at = NOW()
            WHERE m8_ai_scan_quota.scan_count < ?
            RETURNING scan_count
        ');
        $stmt->execute([$userId, $limit]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Atomically check quota and increment. Returns false if limit is already reached.
     * Caller must NOT wrap this in their own transaction.
     * @deprecated Use canScanToday() + incrementScanCount() separately for better error handling
     */
    public function checkAndIncrement(int $userId, int $limit): bool
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare('
            SELECT scan_count
            FROM m8_ai_scan_quota
            WHERE user_id = ? AND log_date = CURRENT_DATE
            FOR UPDATE
        ');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentCount = $row ? (int) $row['scan_count'] : 0;

        if ($currentCount >= $limit) {
            $this->pdo->rollBack();
            return false;
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO m8_ai_scan_quota (user_id, log_date, scan_count)
            VALUES (?, CURRENT_DATE, 1)
            ON CONFLICT (user_id, log_date) DO UPDATE
                SET scan_count = m8_ai_scan_quota.scan_count + 1,
                    updated_at = NOW()
        ');
        $stmt->execute([$userId]);
        $this->pdo->commit();

        return true;
    }
}
