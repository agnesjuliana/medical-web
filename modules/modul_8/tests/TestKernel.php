<?php
/**
 * TestKernel.php
 *
 * Registers a php://input stream wrapper so tests can inject request bodies,
 * and provides the MockPdo class used across all test suites.
 *
 * Must be the very first file loaded by runner.php.
 */

// ──────────────────────────────────────────────────────────────────────────────
// 1.  php://input stream wrapper
// ──────────────────────────────────────────────────────────────────────────────

class MockInputStream
{
    private static string $buffer = '';
    private int $position = 0;

    public static function setBody(string $json): void
    {
        self::$buffer = $json;
    }

    public function stream_open($path, $mode, $options, &$opened_path): bool
    {
        $this->position = 0;
        return true;
    }

    public function stream_read(int $count): string
    {
        $chunk = substr(self::$buffer, $this->position, $count);
        $this->position += strlen($chunk);
        return $chunk;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen(self::$buffer);
    }

    public function stream_stat(): array
    {
        return [];
    }
}

stream_wrapper_unregister('php');
stream_wrapper_register('php', 'MockInputStream');

// ──────────────────────────────────────────────────────────────────────────────
// 2.  MockPdo — a complete, query-queue-based PDO mock
// ──────────────────────────────────────────────────────────────────────────────

/**
 * MockStatement models a single prepared statement.
 */
class MockStatement
{
    /** @var MockPdo */
    private $pdo;
    public array $lastParams = [];

    public function __construct(MockPdo $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(array $params = []): bool
    {
        $this->lastParams       = $params;
        $this->pdo->lastParams  = $params;
        $this->pdo->lastQuery   = $this->pdo->lastPreparedSql;
        $this->pdo->queries[]  = ["sql" => $this->pdo->lastPreparedSql, "params" => $params];
        return true;
    }

    public function fetch(int $mode = 0)
    {
        return $this->pdo->shiftRow();
    }

    public function fetchAll(int $mode = 0): array
    {
        return $this->pdo->shiftAllRows();
    }

    public function rowCount(): int
    {
        return $this->pdo->rowCount;
    }

    public function bindValue($param, $value, $type = null): bool
    {
        return true;
    }
}

/**
 * MockPdo — call enqueueRows() for each query that will be executed.
 * Each call to shiftRow() / shiftAllRows() consumes the next queued batch.
 */
class MockPdo
{
    /** @var array[] Queue of row-batches; each batch is consumed by one fetch/fetchAll */
    private array $queue = [];

    public ?string $lastQuery      = null;
    public ?string $lastPreparedSql = null;
    public array   $lastParams     = [];
    public int     $rowCount       = 1;

    /** Push a batch of rows that will be consumed by the next fetch call */
    public function enqueueRows(array $rows): void
    {
        $this->queue[] = $rows;
    }

    public array   $queries        = [];
    /** Shorthand: enqueue a single row */
    public function enqueueRow(array $row): void
    {
        $this->queue[] = [$row];
    }

    /** Shorthand: enqueue "empty result" (no row found) */
    public function enqueueEmpty(): void
    {
        $this->queue[] = [];
    }

    public function shiftRow()
    {
        if (empty($this->queue)) {
            return false;
        }
        $batch = array_shift($this->queue);
        return $batch[0] ?? false;
    }

    public function shiftAllRows(): array
    {
        if (empty($this->queue)) {
            return [];
        }
        return array_shift($this->queue);
    }

    public function prepare(string $sql): MockStatement
    {
        $this->lastPreparedSql = $sql;
        return new MockStatement($this);
    }

    public function beginTransaction(): bool { return true; }
    public function commit(): bool          { return true; }
    public function rollBack(): bool        { return true; }
}

// ──────────────────────────────────────────────────────────────────────────────
// 3.  Assertion helpers (global, no PHPUnit)
// ──────────────────────────────────────────────────────────────────────────────

$GLOBALS['__test_results'] = [];

function assert_true(string $label, bool $condition, string $detail = ''): void
{
    $GLOBALS['__test_results'][] = [
        'label'     => $label,
        'pass'      => $condition,
        'detail'    => $detail,
    ];

    $icon   = $condition ? "\033[32m✓\033[0m" : "\033[31m✗\033[0m";
    $suffix = (!$condition && $detail) ? "  → $detail" : '';
    echo "  $icon  $label$suffix\n";
}

function assert_equals(string $label, $expected, $actual): void
{
    $ok = $expected == $actual;
    assert_true($label, $ok, $ok ? '' : "expected=$expected, actual=$actual");
}

function assert_contains(string $label, string $needle, string $haystack): void
{
    $ok = str_contains($haystack, $needle);
    assert_true($label, $ok, $ok ? '' : "\"$needle\" not found in \"$haystack\"");
}

function assert_key(string $label, string $key, array $arr): void
{
    assert_true($label, array_key_exists($key, $arr), "key '$key' missing from array");
}

function print_suite_header(string $name): void
{
    echo "\n\033[1;34m══ $name ══\033[0m\n";
}

function print_summary(): void
{
    $results = $GLOBALS['__test_results'];
    $total   = count($results);
    $passed  = count(array_filter($results, fn($r) => $r['pass']));
    $failed  = $total - $passed;

    echo "\n";
    if ($failed === 0) {
        echo "\033[1;32m✔  All $total assertions passed.\033[0m\n";
    } else {
        echo "\033[1;31m✘  $failed/$total assertions FAILED.\033[0m\n";
    }
}

function get_exit_code(): int
{
    $results = $GLOBALS['__test_results'];
    foreach ($results as $r) {
        if (!$r['pass']) return 1;
    }
    return 0;
}
