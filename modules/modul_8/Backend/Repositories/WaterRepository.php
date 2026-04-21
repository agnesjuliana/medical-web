<?php

namespace Backend\Repositories;

use PDO;

class WaterRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getTotalByDate(int $userId, string $date): int
    {
        $stmt = $this->pdo->prepare('
            SELECT COALESCE(SUM(amount_ml), 0) as water_ml
            FROM m8_water_logs
            WHERE user_id = ? AND log_date = ?
        ');
        $stmt->execute([$userId, $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['water_ml'];
    }

    public function insert(int $userId, string $date, int $amountMl): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO m8_water_logs (user_id, log_date, amount_ml, logged_at, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW(), NOW())
            RETURNING id
        ');
        $stmt->execute([$userId, $date, $amountMl]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['id'];
    }
}
