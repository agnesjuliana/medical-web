<?php
/**
 * AiScanTest.php
 * 
 * Negative Case: Rate Limit (429 Error).
 */

class AiScanTest extends ApiTestBase {
    
    public function testAiScanRateLimit() {
        $this->setupSession(['user_id' => 1]);
        
        $pdo = $this->mockPdo;
        // Mock quota reached (20)
        $pdo->rows = [['scan_count' => 20]];
        
        $body = [
            'image_b64' => 'data:image/jpeg;base64,mockdata'
        ];

        // We expect json_error which throws Exception('API_EXIT')
        // The runner catches Exception and reports failure if it's not API_EXIT
        // But we want to capture the status and message.
        
        $response = $this->invokeApi('ai_scan_food', [], [], $body);
        
        report("AiScanTest::testAiScanRateLimit", 
            isset($response['error']) && str_contains($response['error'], 'limit reached'),
            "Expected rate limit error, got: " . json_encode($response)
        );
    }
}
