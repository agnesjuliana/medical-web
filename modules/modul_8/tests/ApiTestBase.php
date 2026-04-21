<?php
/**
 * ApiTestBase.php
 * 
 * Mocking utilities for testing api.php without side effects.
 */

class ApiTestBase {
    public $mockPdo;
    public $mockSession = [];
    public $lastHttpStatus = 200;
    public $echoOutput = '';

    public function __construct() {
        $this->mockPdo = $this->createMockPdo();
    }

    public function setupSession(array $data) {
        $this->mockSession = $data;
    }

    public function invokeApi(string $action, array $get = [], array $post = [], $body = null) {
        // Prepare environment
        $_GET = array_merge(['action' => $action], $get);
        $_POST = $post;
        $_SESSION = $this->mockSession;
        
        // Mock php://input
        if ($body !== null) {
            $tempFile = tempnam(sys_get_temp_dir(), 'api_test_body');
            file_put_contents($tempFile, json_encode($body));
            $this->mockPhpInput($tempFile);
        }

        // Intercept headers and output
        ob_start();
        $this->lastHttpStatus = 200; // Default
        
        // Inject dependencies into local scope for api.php
        $pdo = $this->mockPdo;
        $body = $body; // Already in scope if passed as arg
        
        try {
            require __DIR__ . '/../Backend/index.php';
        } catch (Exception $e) {
            if ($e->getMessage() !== 'API_EXIT') {
                throw $e;
            }
        }
        
        $this->echoOutput = ob_get_clean();
        return json_decode($this->echoOutput, true);
    }

    private function defineMocks() {
        if (!function_exists('http_response_code_mock')) {
            // This is tricky because we can't redefine built-in functions easily
            // We will modify api.php slightly to use a helper for http_response_code if needed,
            // OR we just rely on the fact that we can't easily capture it without a more complex setup.
            // Actually, we can just look at the output if it's JSON.
        }
        
        // We will "hijack" the dependencies by defining them before api.php includes them
        // But api.php uses require_once for config/database.php and core/auth.php
        // So we must define the functions they provide FIRST.
    }

    private function createMockPdo() {
        // Simplified PDO mock
        return new class {
            public $lastQuery;
            public $lastParams;
            public $rows = [];
            public $rowCount = 1;

            public function prepare($sql) {
                $this->lastQuery = $sql;
                return new class($this) {
                    private $parent;
                    public function __construct($parent) { $this->parent = $parent; }
                    public function execute($params = []) { $this->parent->lastParams = $params; return true; }
                    public function fetch() { return array_shift($this->parent->rows); }
                    public function fetchAll() { return $this->parent->rows; }
                    public function rowCount() { return $this->parent->rowCount; }
                    public function bindValue($i, $v, $t = null) {}
                };
            }
            public function beginTransaction() {}
            public function commit() {}
            public function rollBack() {}
        };
    }

    private function mockPhpInput($file) {
        // This is hard to do in pure PHP without a stream wrapper
        // We'll skip for now and see if we can just pass $body to a modified api.php
    }
}
