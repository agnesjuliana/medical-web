<?php
/**
 * DashboardTest.php
 * 
 * Edge Case: Health Score clamping.
 */

class DashboardTest extends ApiTestBase {
    
    public function testHealthScoreClamping() {
        $this->setupSession(['user_id' => 1]);
        
        $pdo = $this->mockPdo;
        // Mock profile and extreme meal totals
        // 1. Profile
        $pdo->rows[] = [
            'daily_calorie_target' => 2000,
            'daily_protein_g' => 150,
            'daily_carbs_g' => 200,
            'daily_fats_g' => 60
        ];
        // 2. Meal totals (Extreme: 10000 calories)
        $pdo->rows[] = [
            'calories' => 10000,
            'protein_g' => 500,
            'carbs_g' => 1000,
            'fats_g' => 300,
            'fiber_g' => 0
        ];
        // 3. Water logs
        $pdo->rows[] = ['water_ml' => 0];
        // 4. Recent meals (fetchAll expects array of rows, we return an empty array)
        $pdo->rows[] = []; 
        
        $response = $this->invokeApi('get_dashboard', ['date' => date('Y-m-d')]);
        
        $score = $response['data']['health_score'] ?? -1;
        
        report("DashboardTest::testHealthScoreClamping", 
            $score >= 0 && $score <= 100,
            "Expected score 0-100 for extreme deviation, got: $score"
        );
        
        // With 400% deviation, score should be low but clamped at 0
        report("DashboardTest::verifyLowScore", $score < 50, "Expected low score for 10k calories, got: $score");
    }
}
