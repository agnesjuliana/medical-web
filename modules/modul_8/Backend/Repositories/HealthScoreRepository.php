<?php

namespace Backend\Repositories;

use PDO;

class HealthScoreRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function upsert(int $userId, string $date, int $score, float $calDev, float $macroDev): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO m8_daily_health_scores
                (user_id, log_date, score, calorie_deviation_pct, macro_deviation_pct)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT (user_id, log_date) DO UPDATE SET
                score                 = EXCLUDED.score,
                calorie_deviation_pct = EXCLUDED.calorie_deviation_pct,
                macro_deviation_pct   = EXCLUDED.macro_deviation_pct,
                computed_at           = NOW(),
                updated_at            = NOW()
        ');
        $stmt->execute([$userId, $date, $score, $calDev, $macroDev]);
    }

    public function getRecent(int $userId, int $days): array
    {
        $stmt = $this->pdo->prepare('
            SELECT log_date, score, calorie_deviation_pct,
                   macro_deviation_pct, computed_at
            FROM m8_daily_health_scores
            WHERE user_id = ?
            ORDER BY log_date DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $days, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['score']                 = (int)   $row['score'];
            $row['calorie_deviation_pct'] = round((float) $row['calorie_deviation_pct'], 2);
            $row['macro_deviation_pct']   = round((float) $row['macro_deviation_pct'], 2);
        }
        unset($row);

        return $rows;
    }
}
