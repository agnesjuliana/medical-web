<?php

namespace Backend\Repositories;

use PDO;

class WeightRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get weight logs for the past N days (inclusive).
     * Uses parameterized query to prevent SQL injection on the interval.
     */
    public function getRecentLogs(int $userId, int $days = 90): array
    {
        $stmt = $this->pdo->prepare('
            SELECT weight_kg, log_date
            FROM m8_weight_logs
            WHERE user_id = ? AND log_date >= CURRENT_DATE - (? || \' days\')::INTERVAL
            ORDER BY log_date ASC
        ');
        $stmt->execute([$userId, $days]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['weight_kg'] = (float) $r['weight_kg'];
        }
        unset($r);
        return $rows;
    }

    /**
     * Get all weight logs for a user with optional range filter.
     * Range options: '90d', '6m', '1y', 'all'
     */
    public function getLogsByRange(int $userId, string $range = '90d'): array
    {
        $ranges = [
            '90d' => 90,
            '6m' => 180,
            '1y' => 365,
            'all' => null
        ];

        $days = $ranges[$range] ?? 90;

        if ($days === null) {
            // Get all logs
            $stmt = $this->pdo->prepare('
                SELECT log_date, weight_kg
                FROM m8_weight_logs
                WHERE user_id = ?
                ORDER BY log_date ASC
            ');
            $stmt->execute([$userId]);
        } else {
            // Get logs for the specified number of days
            $stmt = $this->pdo->prepare('
                SELECT log_date, weight_kg
                FROM m8_weight_logs
                WHERE user_id = ? AND log_date >= CURRENT_DATE - (? || \' days\')::INTERVAL
                ORDER BY log_date ASC
            ');
            $stmt->execute([$userId, $days]);
        }

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['weight_kg'] = (float) $r['weight_kg'];
        }
        unset($r);
        return $rows;
    }

    /**
     * Get the earliest recorded weight (for "Start" display).
     */
    public function getFirstLog(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT weight_kg, log_date
            FROM m8_weight_logs
            WHERE user_id = ?
            ORDER BY log_date ASC
            LIMIT 1
        ');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Insert a new weight log entry and update the profile snapshot.
     * Runs inside a transaction to guarantee atomicity.
     */
    public function insertAndUpdateProfile(int $userId, float $weightKg, string $date): int
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Log to history
            $stmt = $this->pdo->prepare('
                INSERT INTO m8_weight_logs (user_id, weight_kg, log_date, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
                RETURNING id
            ');
            $stmt->execute([$userId, $weightKg, $date]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Update profile snapshot
            $stmt = $this->pdo->prepare('
                UPDATE m8_user_profiles
                SET weight_kg = ?, updated_at = NOW()
                WHERE user_id = ?
            ');
            $stmt->execute([$weightKg, $userId]);

            $this->pdo->commit();
            return (int) $row['id'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
