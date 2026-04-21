<?php
/**
 * IntegrationBase.php
 *
 * Thin wrapper that boots the Backend/index.php dispatcher with
 * injected MockPdo + captured output. Each test creates a fresh instance.
 *
 * Key improvements over ApiTestBase:
 * - Uses php://input stream wrapper from TestKernel (body JSON actually readable)
 * - Uses queue-based MockPdo (proper multi-query simulation)
 * - No global state leaks between tests
 */

// TestKernel loaded by runner.php

class IntegrationBase
{
    public MockPdo $pdo;

    public function __construct()
    {
        $this->pdo = new MockPdo();
    }

    /**
     * @param string $action   GET ?action=
     * @param array  $get      Extra GET params
     * @param array  $body     JSON body (written to mock php://input)
     */
    public function call(string $action, array $get = [], ?array $body = null): array
    {
        // Inject into superglobals
        $_GET     = array_merge(['action' => $action], $get);
        $_POST    = [];
        $_SESSION = ['user_id' => 1];

        // Write body into stream wrapper
        MockInputStream::setBody($body !== null ? json_encode($body) : '');

        // Expose PDO to the dispatcher's local scope
        $pdo = $this->pdo;

        ob_start();
        set_error_handler(fn() => true); // suppress header-already-sent in CLI
        try {
            require __DIR__ . '/../../Backend/index.php';
        } catch (\Exception $e) {
            restore_error_handler();
            if ($e->getMessage() !== 'API_EXIT') {
                ob_end_clean();
                throw $e;
            }
        }
        restore_error_handler();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        if ($decoded === null) {
            throw new \RuntimeException("Non-JSON output from dispatcher:\n$output");
        }
        return $decoded;
    }
}
